<?php
$buttons = array('side' => array(), 'bottom' => array());

// Parse buttons
foreach(Arr::get($details, 'list_buttons') as $button => $options) {
    
    // Predefined buttons
    if(is_numeric($button)) {
        $button = $options;
        $options = array();
        
        $options['button'] = Arr::get(array(
            'add' => array(
                'href' => '/admin/edit/:model',
                'class' => 'btn btn-success',
                'target' => '_blank',
            ),
            'edit' => array(
                'href' => '/admin/edit/:model/:id',
                'class' => 'btn btn-primary',
                'target' => '_blank',
            ),
            'delete' => array(
                'onclick' => "confirm_delete(':model', :id)",
                'class' => 'btn btn-danger',
                'href' => '#!',
            )
        ), $button, array());
        
        $options['icon'] = Arr::get(array(
            'add' => 'glyphicon glyphicon-plus',
            'edit' => 'glyphicon glyphicon-edit',
            'delete' => 'glyphicon glyphicon-remove',
        ), $button, '');
        
        $options['position'] = Arr::get(array(
            'add' => 'bottom',
            'edit' => 'side',
            'delete' => 'side',
        ), $button, '');
    }
    
    // Substitutes model name in attributes
    foreach($options['button'] as $attr => $value) {
        $options['button'][$attr] = str_replace(':model', $model_name, $value);
    }
    $button = 'general.'.$button;
    $buttons[$options['position']][$button] = $options;
}

$list_fields = Arr::get($details, 'list_fields', array('__toString'));
?>

<table class="table table-striped" data-model="<?php echo $model_name ?>">
    <thead>
        <tr>
            <?php foreach($list_fields as $field): ?>
                <th><?php echo __('model.'.strtolower($model_name).'.'.$field); ?></th>
            <?php endforeach ?>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($models->find_all() as $element): ?>
            <tr data-id="<?php echo $element->pk() ?>">
                <?php foreach($list_fields as $field): ?>
                    <?php if(method_exists($element, $field)): ?>
                        <td><?php echo $element->{$field}(); ?></td>
                    <?php else: ?>
                        <td><?php echo $element->{$field}; ?></td>
                    <?php endif ?>
                <?php endforeach ?>

                <td class="text-right">
                    <div class="btn-group">
                        <?php
                        foreach(Arr::get($buttons, 'side', array()) as $label => $options) {
                            foreach($options['button'] as $attr => $value) {
                                $options['button'][$attr] = str_replace(':id', $element->pk(), $value);
                            }
                            $icon = Arr::get($options, 'icon') ? '<i class="'.$options['icon'].'"></i> ' : '';
                            echo '<a'.HTML::attributes($options['button']).'>'.$icon.__($label).'</a>';
                        }
                        ?>
                    </div>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>

<?php
foreach(Arr::get($buttons, 'bottom', array()) as $label => $options) {
    $icon = Arr::get($options, 'icon') ? '<i class="'.$options['icon'].'"></i> ' : '';
    echo '<a'.HTML::attributes($options['button']).'>'.$icon.__($label).'</a>';
}
?>
