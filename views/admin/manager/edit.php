<?php
$browse_for_fields = Kohana::$config->load('admin.browse_for_fields');
$ignored_fields = Kohana::$config->load('admin.ignored_fields');
?>
<form method="POST" action="#" id="edit" class="col-xs-12">
    <table class="table table-striped">
        <thead>
            <tr>
                <th><?php echo __('general-name'); ?></th>
                <th><?php echo __('general-value'); ?></th>
            </tr>
        </thead>

        <tbody>
            <?php
            foreach($element->table_columns() as $name => $infos) {
                if($infos['key'] == 'PRI' || in_array($name, Arr::get($ignored_fields, $element->object_name(), array()))) {
                    continue;
                }
            ?>
                <tr class="form-group">
                    <td>
                        <label for="<?php echo $name; ?>">
                            <?php echo __('model.'.$element->object_name().'.'.$name); ?>
                        </label>
                    </td>

                    <td style="width: 40%;">
                        <?php
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
                                            $select_options[$rel->pk()] = (String) $rel;
                                        }

                                        echo Form::select($name, $select_options, $element->{$name}, array('class' => 'form-control',
                                                                                                              'id'    => $name));
                                        $hasForeignKey = true;
                                    } else {
                                        echo __('general-too_many_entries');
                                    }
                                }
                            }
                        }

                        if(!$hasForeignKey) {
                            if(in_array($name, $browse_for_fields)) {
                                echo Form::hidden($name, $element->{$name}, array('id' => $name));

                                echo '<a href="#!" onclick="window.open(\'/admin/search_panel/'.$name.'/'.$id.'\', \'dataitem\', \'toolbar=no,menubar=no,scrollbars=yes,width=700px,height=500px\');">
                                          Parcourir la liste <i class="glyphicon glyphicon-search"></i>
                                      </a>';
                            } else if(strstr($infos['data_type'], 'tinyint')) {
                                // Boolean data.
                                echo '<label style="font-weight: normal;">'.Form::radio($name, 0, $element->loaded() ? $element->{$name} != 1 : false).' '.__('general-no').'</label>&nbsp;&nbsp;&nbsp;'.
                                     '<label style="font-weight: normal;">'.Form::radio($name, 1, $element->{$name} == 1, array('id' => $name)).' '.__('general-yes').'</label>';

                            } else if(strstr($infos['data_type'], 'float')) {
                                // For floating numbers.
                                echo Form::input($name, $element->{$name}, array('type'  => 'number',
                                                                                    'step'  => 'any',
                                                                                    'class' => 'form-control',
                                                                                    'id'    => $name));

                            } else if(strstr($infos['data_type'], 'int')) {
                                // For numbers.
                                echo Form::input($name, $element->{$name}, array('type'  => 'number',
                                                                                    'class' => 'form-control',
                                                                                    'id'    => $name));
                            } else if(strstr($infos['data_type'], 'enum')) {
                                // A select box for enum values.
                                $keys = $element->enum_field_values($name);
                                $options = array();

                                foreach($keys as $key) {
                                    $options[$key] = __('model.'.$element->object_name().'.'.$name.'.'.$key);
                                }

                                echo Form::select($name, $options, $element->{$name}, array('class' => 'form-control',
                                                                                               'id'    => $name));

                            } else if(strstr($infos['data_type'], 'datetime')) {
                                // Date inputs.
                                echo Form::input($name, $element->{$name}, array('placeholder' => 'AAAA-MM-JJ HH:mm:ss',
                                                                                    'class'       => 'datetimepicker form-control',
                                                                                    'id'          => $name));

                            } else if(strstr($infos['data_type'], 'date')) {
                                // Date inputs.
                                echo Form::input($name, $element->{$name}, array('placeholder' => 'AAAA-MM-JJ',
                                                                                    'class'       => 'datepicker form-control',
                                                                                    'id'          => $name));
                            } else if(strstr($infos['data_type'], 'tinytext')) {
                                // File upload.
                                echo '<div class="input-group">';

                                    echo Form::input($name, $element->{$name}, array('class'       => 'form-control',
                                                                                        'placeholder' => 'http://...',
                                                                                        'id'          => $name));

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
                                                echo Form::textarea($name, $element->{$name}, array('class' => 'form-control ckeditor',
                                                                                                       'id'    => $name));
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
                                echo Form::input($name, $element->{$name}, array('class' => 'form-control',
                                                                                    'id'    => $name));
                                
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
        if(@$options['through'] != '') {
            $has_many_through = true;
        }
    }
    
    $model = $element->object_name();
    
    if($has_many_through) {
    ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th style="width: 50%;"><?php echo __('model.'.lcfirst($model).'.has_many_through.name') ?></th>
                    <th style="width: 50%;"><?php echo __('model.'.lcfirst($model).'.has_many_through.associations') ?></th>
                </tr>
            </thead>

            <tbody>
                <?php
                foreach($element->has_many() as $relation => $options) {
                    if(@$options['through'] == '') {
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
                            echo Form::select($relation.'[]', ORM::factory(ucfirst($options['model']))->find_all()
                                     ->as_array('id', null), $element->{$relation}->find_all()->as_array(null, 'id'), array('multiple' => true, 'style' => 'width: 100%;'));
                            ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>

    <?php
    echo Form::hidden('model', $model). // TODO : Is this necessary ?
         Form::submit(null, __('general.submit'), array('class' => 'form-control btn btn-success'));

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
$hashed_inputs = Kohana::$config->load('admin.hashed_fields.'.$element->object_name()) ?: array();
// Use array("field" => "field", ...) instead of array(0 => "field", ...)
$hashed_inputs = array_combine($hashed_inputs, $hashed_inputs);
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var hashed_inputs = <?php echo json_encode($hashed_inputs) ?>;
        
        $('form#edit').submit(function() {
            for(var input in hashed_inputs) {
                $('#'+input, this).prop('disabled', true);
            }
        });
        
        // Hashed inputs
        for(var input in hashed_inputs) {
            $('#' + input).val('(cliquez pour modifier)').click(function() {
                $(this).val('');
                delete hashed_inputs[input];
            });
        };
    });
</script>
