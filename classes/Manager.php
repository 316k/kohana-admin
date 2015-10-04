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
    
    public function action_manager() {
        
        // No model : list available models
        if(!$this->request->param('model')) {
            
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

            $this->content = View::factory('admin/manager/models', array('classes' => $classes));
            return;
        }
        
        $model_name = $this->request->param('model');
        $element = ORM::factory($model_name, $this->request->param('id'));
        
        $this->title = __('pagename-admin-manager', array(':name' => $model_name));
        
        $redirect = Kohana::$config->load('admin.redirect');
        $redirect = URL::site(str_replace(':model', $model_name, $redirect), 'http');

        if($this->request->param('mode') == 'edit' && $this->request->method() == Request::POST) {
            
            $posted_values = $this->request->post();
            
            // "has many through" list.
            $has_many_through_list = array();
            
            foreach($element->has_many() as $relation => $options) {
                if(@$options['through'] != '') {
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
                    $element->add($key, $posted_value);
                }
            }

            try {
                $element->values($posted_values)->save();
                
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
        } elseif($this->request->param('mode') == 'delete') {
            
            Notification::instance()->add('success', __('success-delete', array(':name' => (string) $element)));
            $element->delete();
            $this->redirect($redirect);
            
        }
        
        $this->content = View::factory("admin/manager/" . $this->request->param('mode'), array(
            'models' => ORM::factory($model_name)->find_all(),
            'element' => $element,
            'model_name' => $model_name,
        ));
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
        $role = ORM::factory('roles_users', 1);
        $user->add($role);
    }
}
