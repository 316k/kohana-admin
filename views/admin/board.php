<?php
$default_icon = 'glyphicon glyphicon-info-sign';
$default_tab = Kohana::$config->load('admin.default_tab') ?: Arr::get(array_keys($modules), 0);

$i = $j = 0;
?>

<ul id="tabs" class="nav nav-tabs" style="margin-bottom: 30px;">
    <?php foreach($modules as $module => $details) { ?>
        <li<?php echo $default_tab == $module ? ' class="active"' : '' ?>>
            <a href="<?php echo @$details['href'] ?: '#tab'.ucfirst($module); ?>" <?php echo isset($details['href']) ? 'target="_blank"' : 'data-toggle="tab"'; ?>>
                <i class="<?php echo Arr::get($details, 'icon', $default_icon); ?>"></i>

                <span class="hidden-xs hidden-print" style="margin-left: 10px;">
                    <?php echo __('admin-main-'.strtolower($module)); ?>
                </span>
            </a>
        </li>
    <?php $i++; } ?>
</ul>

<div class="tab-content">
    <?php
    foreach($modules as $module => $details) {
        if(isset($details['href'])) {
            continue;
        }

        $models = Arr::get($details, 'models', ORM::factory($module));
    ?>
        <div class="tab-pane<?php echo $default_tab == $module ? ' active' : '' ?>" id="tab<?php echo ucfirst($module); ?>">
            <div class="row">
                <p class="col-md-11">
                    <?php echo __('admin-main-'.strtolower($module).'_abs'); ?>
                </p>

                <div class="col-md-1 hidden-xs hidden-sm">
                    <i class="<?php echo @$details['icon'] ?: $default_icon; ?> link-color" style="font-size: 50px;"></i>
                </div>
            </div>

            <br /><br />
            
            <?php
            foreach(Arr::get($details, 'views', array()) as $view) {

                // TODO : Clean code by removing deprecated stuff
                // XXX : Backward compatibility with "module:" (deprecated)
                if(strpos($view, 'module:') !== 0) {
                    echo View::factory('admin/board/'.$view, array(
                        'model_name' => $module,
                        'details' => $details,
                        'models' => $models
                    ));
                    
                // XXX : Deprecated
                } else if(strpos($view, 'module:') === 0) {
                    // If it's a module, this is the code to be executed.
                    switch(str_replace('module:', '', $view)) {
                        case 'TEXT':
                            // The text module just shows whatever string defined.
                            $text = preg_replace_callback('#{i18n:(.*)}#is', function($match) {
                                        return __($match[1]);
                                    }, $details['text_string']);
                            
                            echo isset($details['text_string']) ? '<p>'.$text.'</p>' : '';
                            
                            break;
                        
                        case 'FILTER':
                            echo View::factory('admin/filter')->bind('model', $module);
                            
                            break;
                        
                        case 'LIST':
                            // The list module shows a table list of all elements in the model.
                            
                            $sideButtons = '';
                            $bottomButtons = '';
                            
                            foreach(@$details['list_buttons'] as $button => $options) {
                                if(is_numeric($button)) {
                                    $button = $options;
                                }
                                
                                if(strpos($button, 'predef:') === 0) {
                                    switch(str_replace('predef:', '', $button)) {
                                        case 'ADD':
                                            $bottomButtons .= '<a href="/admin/edit/'.$module.'" class="btn btn-success" target="_blank"><i class="glyphicon glyphicon-plus"></i> '.__('general-add').'</a>';
                                            
                                            break;
                                        
                                        case 'EDIT':
                                            $sideButtons .= '<a href="/admin/edit/'.$module.'/{id}" class="btn btn-primary" target="_blank"><i class="glyphicon glyphicon-edit"></i></a>';
                                            
                                            break;
                                        
                                        case 'DELETE':
                                            $sideButtons .= '<a href="javascript:confirm_delete(\''.$module.'\', {id});" class="btn btn-danger"><i class="glyphicon glyphicon-remove"></i></a>';
                                            
                                            break;
                                        
                                        case 'INFOS':
                                            $sideButtons .= '<a href="/'.$module.'/see/{id}" class="btn btn-warning" target="_blank">'.__('general-more_info').'</a>';
                                            
                                            break;
                                        
                                        // FIXME : Not implemented
                                        case 'DUPLICATE':
                                            $sideButtons .= '<a href="/admin/duplicate/'.$module.'/{id}" class="btn btn-info" target="_blank"><i class="glyphicon glyphicon-file"></i></a>';
                                            
                                            break;
                                    }
                                } else {
                                    // Custom buttons.
                                    $i18n = $button;
                                    $level = $options[0];
                                    $href = str_replace('{model}', $module, $options[1]);
                                    
                                    $add = !strstr($href, 'javascript:') ? ' target="_blank"' : '';
                                    
                                    $sideButtons .= '<a href="'.$href.'" class="btn btn-'.$level.'"'.$add.'>'.__($i18n).'</a>';
                                }
                            }
                            ?>
                            
                            <table class="table table-striped" data-model="<?php echo $module ?>">
                                <thead>
                                    <tr>
                                        <th><?php echo __('general-name'); ?></th>
                                        <th></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php
                                    foreach($models->find_all() as $element) {
                                    ?>
                                        <tr data-id="<?php echo $element->pk() ?>">
                                            <td><?php echo $element; ?></td>

                                            <td style="text-align: right;">
                                                <?php
                                                $eid_param = '{events_ids}';
                                                
                                                // Custom rule for event IDs in activities (really, really didn't want it to come to this...).
                                                if(ucfirst($module) == 'Activite') {
                                                    $events_ids = $element->evenements->find_all()->as_array(null, 'id');
                                                    
                                                    switch(count($events_ids)) {
                                                        case 1:
                                                            $eid_param = $events_ids[0];
                                                            break;
                                                            
                                                        case 0:
                                                            $eid_param = 0;
                                                            break;
                                                            
                                                        default:
                                                            $eid_param = '['.implode(', ', $events_ids).']';
                                                    }
                                                }
                                                // End custom rule (please delete this last block for generic usages).
                                                
                                                echo str_replace(array('{id}', '{events_ids}'), array($element->id, $eid_param), $sideButtons);
                                                ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
            
                            <?php
                            echo $bottomButtons;
                            
                            break;
                    }
                }
            }
            ?>
        </div>
    <?php $j++; } ?>
</div>
<script>
function confirm_delete(model, id) {
    if(confirm("Voulez-vous vraiement supprimer l'élément sélectionné ?")) {
        window.location = '/admin/delete/' + model + '/' + id;
    }
}
</script>
