<?php
$browse_for_fields = Kohana::$config->load('admin.browse_for_fields');
$ignored_fields = Arr::get(Kohana::$config->load('admin.ignored_fields'), $element->object_name(), array());
$upload_fields = Arr::get(Kohana::$config->load('admin.upload_fields'), $element->object_name(), array());
$fields_attributes = Arr::get(Kohana::$config->load('admin.fields_attributes'), $element->object_name(), array());

$form = array(
    'id' => 'edit',
    'class' => 'col-xs-12',
);

if($upload_fields)
    $form['enctype'] = 'multipart/form-data';

echo Form::open(NULL, $form);
?>
<p><a class="btn btn-warning" href="#!" onclick="window.history.back()"><i class="glyphicon glyphicon-arrow-left"></i> <?php echo __('general.back') ?></a></p>

    <table class="table table-striped">
        <thead>
            <tr>
                <th><?php echo __('general.name'); ?></th>
                <th><?php echo __('general.value'); ?></th>
            </tr>
        </thead>

        <tbody>
            <?php
            foreach($element->table_columns() as $name => $infos) {
                if($infos['key'] == 'PRI' || in_array($name, $ignored_fields)) {
                    continue;
                }
                $attr = Arr::get($fields_attributes, $name, array()) + array('id' => $name);
            ?>
                <tr class="form-group">
                    <td>
                        <label for="<?php echo $name; ?>">
                            <?php echo __('model.'.$element->object_name().'.'.$name); ?>
                        </label>
                    </td>

                    <td style="width: 40%;">
                        
                        <?php
                        // TODO : Rewrite this
                        $hasForeignKey = false;

                        // Finds all the values for the foreign data (primary key as label and
                        // _toString() value as name).
                        if(!in_array($name, $browse_for_fields)) {
                            foreach($element->belongs_to() as $relation => $options) {
                                if($name == $options['foreign_key']) {
                                    if(ORM::factory(ucfirst($options['model']))->count_all() <= 500) {
                                        $relations = ORM::factory(ucfirst($options['model']))->limit(500)->find_all();
                                        $select_options = array();

                                        if($infos['is_nullable']) {
                                            $select_options[Kohana::$config->load('admin.null_value')] = '['.__('general-none').']';
                                        }

                                        foreach($relations as $rel) {
                                            $select_options[$rel->pk()] = (string) $rel;
                                        }

                                        echo Form::select($name, $select_options, $element->{$name}, $attr + array('class' => 'form-control'));
                                        $hasForeignKey = true;
                                    } else {
                                        echo __('general-too_many_entries');
                                    }
                                }
                            }
                        }

                        if(!$hasForeignKey) {
                            if(in_array($name, $upload_fields)) {
                                echo Form::file($name, $attr + array(
                                    'style' => 'width: 75%; display: inline;'
                                ));
                                
                                if($element->{$name}) {
                                    echo '<a target="_blank" href="/assets/img-upload/'.$element->{$name}.'">
                                            <abbr title="'.__('general.file.uploaded').'">
                                                <i class="glyphicon glyphicon-ok-sign" style="display: inline; color: green;"></i> '.
                                                __('general.file.see').
                                           '</abbr>
                                          </a>';
                                }
                            } else if(in_array($name, $browse_for_fields)) {
                                echo Form::hidden($name, $element->{$name}, $attr);

                                echo '<a href="#!" onclick="window.open(\'/admin/search_panel/'.$name.'/'.$id.'\', \'dataitem\', \'toolbar=no,menubar=no,scrollbars=yes,width=700px,height=500px\');">
                                          Parcourir la liste <i class="glyphicon glyphicon-search"></i>
                                      </a>';
                            } else if(strstr($infos['data_type'], 'tinyint')) {
                                // Boolean data.
                                echo '<label style="font-weight: normal;">'.Form::radio($name, 0, $element->loaded() && !$element->{$name}).' '.__('general-no').'</label>&nbsp;&nbsp;&nbsp;'.
                                     '<label style="font-weight: normal;">'.Form::radio($name, 1, $element->loaded() && !!$element->{$name}, array('id' => $name)).' '.__('general-yes').'</label>';

                            } else if(strstr($infos['data_type'], 'float')) {
                                // For floating numbers.
                                echo Form::input($name, $element->{$name}, $attr + array(
                                    'type'  => 'number',
                                    'step'  => 'any',
                                    'class' => 'form-control',
                                ));

                            } else if(strstr($infos['data_type'], 'int')) {
                                // For numbers.
                                echo Form::input($name, $element->{$name}, $attr + array(
                                    'type'  => 'number',
                                    'class' => 'form-control',
                                ));
                            } else if(strstr($infos['data_type'], 'enum')) {
                                // A select box for enum values.
                                $keys = $element->enum_field_values($name);
                                $options = array();

                                foreach($keys as $key) {
                                    $options[$key] = __('model.'.$element->object_name().'.'.$name.'.'.$key);
                                }

                                echo Form::select($name, $options, $element->{$name}, $attr + array(
                                    'class' => 'form-control',
                                ));

                            } else if(strstr($infos['data_type'], 'datetime')) {
                                // Date inputs.
                                echo Form::input($name, date('Y-m-d G:i', strtotime($element->{$name})), $attr + array(
                                    'placeholder' => 'AAAA-MM-JJ HH:mm',
                                    'class' => 'datetimepicker form-control',
                                ));

                            } else if(strstr($infos['data_type'], 'date')) {
                                // Date inputs.
                                echo Form::input($name, $element->{$name}, $attr + array(
                                    'placeholder' => 'AAAA-MM-JJ',
                                    'class' => 'datepicker form-control',
                                ));
                            } else if(strstr($infos['data_type'], 'tinytext')) {
                                // File upload.
                                echo '<div class="input-group">';

                                    echo Form::input($name, $element->{$name}, $attr + array(
                                        'class' => 'form-control',
                                        'placeholder' => 'http://...',
                                    ));

                                    echo '<a href="javascript:select_file(\'#'.$name.'\');" class="input-group-addon link-color"><i class="glyphicon glyphicon-upload"></i></a>';

                                echo '</div>';

                            } else if(strstr($infos['data_type'], 'longtext')) {
                                // Textarea.
                            ?>
                                <div class="modal fade" id="modal-<?php echo $name; ?>" tabindex="-1" role="dialog" aria-labelledby="label-modal-<?php echo $name; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal">
                                                    <span aria-hidden="true">
                                                        &times;
                                                    </span>

                                                    <span class="sr-only">
                                                        <?php echo __('general-close'); ?>
                                                    </span>
                                                </button>

                                                <h4 class="modal-title" id="label-modal-<?php echo $name; ?>">
                                                    <?php echo __('model.'.$element->object_name().'.'.$name); ?>
                                                </h4>
                                            </div>

                                            <div class="modal-body">
                                                <?php
                                                echo Form::textarea($name, $element->{$name}, $attr + array(
                                                    'class' => 'form-control ckeditor',
                                                ));
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <a href="#!" data-toggle="modal" data-target="#modal-<?php echo $name; ?>">
                                    <i class="glyphicon glyphicon-pencil"></i>
                                    <?php echo __('general-click_to_modify'); ?>
                                </a>
                            <?php
                            } else {
                                // Standard text inputs.
                                echo Form::input($name, $element->{$name}, $attr + array(
                                    'class' => 'form-control',
                                ));
                            }
                        }
                        ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <?php
    // For the "has many through" cases.
    
    $has_many_through = false;
    
    foreach($element->has_many() as $relation => $options) {
        $has_many_through = $has_many_through ||
            (Arr::get($options, 'through') != '' &&
            !in_array($relation, $ignored_fields));
    }
    
    $model = $element->object_name();
    
    if($has_many_through) {
    ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th style="width: 50%;"><?php echo __('model.'.$model.'.has_many_through.name') ?></th>
                    <th style="width: 50%;"><?php echo __('model.'.$model.'.has_many_through.associations') ?></th>
                </tr>
            </thead>

            <tbody>
                <?php
                foreach($element->has_many() as $relation => $options) {
                    if(@$options['through'] == '' || in_array($relation, $ignored_fields)) {
                        continue;
                    }
                ?>
                    <tr class="form-group">
                        <td>
                            <label for="<?php echo $name; ?>">
                                <?php echo __('model.'.lcfirst($model).'.'.$relation); ?>
                            </label>
                        </td>

                        <td>
                            <?php
                            $relations = ORM::factory(ucfirst($options['model']))->find_all()->as_array('id', null);
                            $selected = $element->{$relation}->find_all()->as_array(null, 'id');

                            echo Form::select($relation.'[]', $relations, $selected, $attr + array(
                                'multiple' => true,
                                'style' => 'width: 100%;'
                            ));
                            ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>

    <?php
    echo Form::submit(null, __('general.submit'), array('class' => 'btn btn-success btn-lg', 'style' => 'width: 100%'));

    // Prefilling a field with a GET parameter
    // TODO : Rewrite this
    if((!empty($param2) && !is_numeric($param2) && strstr($param2, '=')) || (!empty($id) && !is_numeric($id) && strstr($id, '='))) {
        $parts = explode('=', $param2 ?: $id);

        $input = $parts[0];
        $value = $parts[1];
    ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                $('#<?php echo $input; ?>').val('<?php echo $value; ?>');
            });
        </script>
    <?php } ?>
</form>
<?php
$protected_inputs = Kohana::$config->load('admin.protected_fields.'.$element->object_name()) ?: array();
// Use array("field" => "field", ...) instead of array(0 => "field", ...)
$protected_inputs = array_combine($protected_inputs, $protected_inputs);
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var protected_inputs = <?php echo json_encode($protected_inputs) ?>;
        
        $('form#edit').submit(function() {
            for(var input in protected_inputs) {
                $('#'+input, this).prop('disabled', true);
            }
        });
        
        // Hashed inputs
        for(var input in protected_inputs) {
            $('#' + input).val(<?php echo json_encode(__('general.click-to-modify')) ?>).click(function() {
                $(this).val('');
                delete protected_inputs[input];
            });
        };
    });
</script>
