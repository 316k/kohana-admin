<?php

trait Manager {
    
    private function filter_modules($modules) {
        return $modules;
    }
    
    /**
     * Main administration page (available when connected only).
     */
    public function action_board() {
        
        $modules = Kohana::$config->load('admin.modules');
        
        $modules = $this->filter_modules($modules);
        
        $this->content = View::factory('admin/board', array('modules' => $modules));
    }
    
    public function action_login() {
        if($this->request->method() === Request::POST) {
        
            if(Auth::instance()->login($this->request->post('user'), $this->request->post('password'))) {
                $this->redirect(URL::site('admin/board', 'http'));
            } else {
                // Wrong credentials.
                $this->redirect(URL::site('admin/login/wrong', 'http'));
            }
        }
        
        $this->content = View::factory('admin/login', array(
            'error' => $this->request->param('id') != NULL,
        ));
    }
    
    public function action_logout() {
        Auth::instance()->logout();
        $this->redirect(URL::site('/', 'http'));
    }

    private function list_classes() {
        $class_names = array_keys(Arr::flatten(Kohana::list_files('classes/Model')));
        $classes = array();
        
        foreach($class_names as $class_name) {
            $class_name = str_replace(array('classes/Model/', '.php', '/'), array('', '', '_'), $class_name);
            
            if(!is_subclass_of('Model_'.$class_name, 'ORM')) {
                continue;
            }
            
            try {
                $model = ORM::factory($class_name);
                $classes[] = $class_name;
            } catch(Database_Exception $dbe) {
                // Ignore field, unused class
            }
        }

        return $classes;
    }

    
    public function manager_list() {

        return View::factory('admin/manager/models', array('classes' => $this->list_classes()));
    }
    
    /**
     * Before edit (before validation)
     */
    private function before_edit($model_name, $element, $posted_values) {
        return $posted_values;
    }
    
    /**
     * If edit was successful
     */
    private function after_edit($model_name, $element) {}

    private function can_edit($model_name, $element) {}
    
    public function action_manager() {
        
        // No model specified : list available models
        if(!$this->request->param('model')) {
            $this->content = $this->manager_list();
            return;
        }
        
        $model_name = $this->request->param('model');
        $element = ORM::factory($model_name, $this->request->param('id'));
        $type = $this->request->param('mode');
        
        $this->title = __('pagename-admin-manager', array(':name' => $model_name));

        if(in_array($type, array('edit', 'delete')) && !$this->can_edit($type, $model_name, $element)) {
            throw new HTTP_Exception_403();
        } else if(!in_array($type, array('edit', 'delete')) && !Auth::instance()->logged_in('admin')) {
            throw new HTTP_Exception_403();
        }
        
        $redirect = Kohana::$config->load('admin.redirect');
        $redirect = URL::site(str_replace(':model', $model_name, $redirect), 'http');

        if($type == 'edit' && $this->request->method() == Request::POST) {
            
            $posted_values = $this->request->post();
            
            // "has many through" list.
            $has_many_through_list = array();
            $has_many_through_elements = array();
            
            foreach($element->has_many() as $relation => $options) {
                if(@$options['through'] != '' && $this->request->post($relation)) {
                    $has_many_through_list[$relation] = $options;
                    
                    foreach(ORM::factory($options['model'])->find_all() as $el) {
                        $element->remove($relation, $el->pk());
                    }
                }
            }
            
            // NULL on purpose.
            foreach($posted_values as $key => $posted_value) {
                if($posted_value == Kohana::$config->load('admin.null_value')) {
                    $posted_values[$key] = NULL;
                }
                
                // "has many through" case.
                if(array_key_exists($key, $has_many_through_list)) {
                    $has_many_through_elements[$key] = $posted_value;
                }
            }

            try {
                
                $posted_values = $this->before_edit($model_name, $element, $posted_values);
                
                $element->values($posted_values)->save();
                
                foreach($has_many_through_elements as $key => $posted_value) {
                    $element->add($key, $posted_value);
                }
                $element->save();
                
                $this->after_edit($model_name, $element);
                
                Notification::instance()->add('success', __('success-edit', array(':name' => (string) $element)));
                
                $this->redirect($redirect);
                
            } catch(ORM_Validation_Exception $e) {
                
                foreach($e->errors(I18n::lang()) as $error) {
                    Notification::instance()->add('danger', $error);
                }
            } catch(Database_Exception $e) {
                if($e->getCode() == 23000) {
                    Notification::instance()->add('danger', __('error-database-duplicate'));
                } else {
                    Notification::instance()->add('danger', __('error-database'));
                }
            }
        } elseif($type == 'delete') {
            
            Notification::instance()->add('success', __('success-delete', array(':name' => (string) $element)));
            $element->delete();
            $this->redirect($redirect);
            
        }
        
        $this->content = View::factory("admin/manager/" . $type, array(
            'models' => ORM::factory($model_name)->find_all(),
            'element' => $element,
            'model_name' => $model_name,
            'query' => $this->request->query(),
        ));
    }
    
    /** Forgotten password helpers **/
    
    private function post_forgot_password($user) {
        // Overload to send an email, notify, redirect, etc.
        // Note that $user might *not* be loaded
    }
    
    public function action_forgot_password() {
        if($this->request->method() === Request::POST) {
            $user = ORM::factory('User', array('email' => $this->request->post('email')));
            
            if($user->loaded()) {
                
                $user->last_password_reset = time();
                $user->password_reset_token = Text::random(NULL, 64);
                
                $user->save();
            }
            $this->post_forgot_password($user);
        }
        
        $this->content = View::factory('admin/forgot_password');
    }
    
    public function action_reset_password() {
        $token = $this->request->param('id');
        $email = Arr::get($_GET, 'email');
        $user = ORM::factory('User', array('password_reset_token' => $token, 'email' => $email));
        
        if(!$user->loaded() || $user->last_password_reset < time() - Date::MINUTE * 20)
            throw new HTTP_Exception_403();
        
        if($this->request->method() === Request::POST) {
            $user->password = $this->request->post('password');
            $user->password_reset_token = NULL;
            
            $user->save();
            $this->redirect(URL::site('admin/login', 'http'));
        }
        
        $this->content = View::factory('admin/reset_password');
    }

    public function action_translations_helper() {
        $this->content = '<pre>';
        
        if(!$this->request->param('id')) {
            
            foreach($this->list_classes() as $class) {
                $this->content .= View::factory('admin/manager/translations-helper', array(
                    'model' => ORM::factory($class),
                )).'<br />';
            }
            
        } else {
            $this->content .= View::factory('admin/manager/translations-helper', array(
                'model' => ORM::factory($this->request->param('id')),
            ));
        }

        $this->content .= '</pre>';
    }
    
    /**
     * Call this to create an arbitrary user, you'll probably need to do this
     * at the beginning of a project
     */
    private function add_user($username, $email, $password) {
        $user = ORM::factory('User');
        $user->email = $email;
        $user->username = $username;
        $user->password = $password;
        $user->save();
        $role = ORM::factory('Role', 1);
        $user->add('roles', $role);
        $role = ORM::factory('Role', 2);
        $user->add('roles', $role);
    }
}
