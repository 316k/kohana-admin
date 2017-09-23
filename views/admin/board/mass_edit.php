<?php
function parse_edit_column($edit_element, $edit_column) {
    $model_and_field = explode('.', $edit_column);

    if(count($model_and_field) == 1) {
        $model = $edit_element;
        $field = $model_and_field[0];
    } else {

        if(is_string($edit_element) || !method_exists($edit_element, $model_and_field[0]))
            $model = $model_and_field[0];
        else
            $model = $edit_element->{$model_and_field[0]}();

        $field = $model_and_field[1];
    }

    return array($model, $field);
}

$models = $details['models'];
$profile = $details['mass_edit_profile'];
$edit_columns = $details['edit_columns'];
$shown_columns = $details['shown_columns'];
?>

<p><?php echo __('mass_edit.'.$profile.'.description') ?></p>

<table class="table table-hover">
    <thead>
        <tr>
            <th>#</th>

            <?php foreach($shown_columns as $field): ?>
                <th>
                    <?php if($field == '__toString'): ?>
                        <?php echo __('general.name') ?>
                    <?php else: ?>
                        <?php echo __('model.'.strtolower($model_name).'.'.$field); ?>
                    <?php endif ?>
                </th>
            <?php endforeach ?>

            <?php foreach($edit_columns as $edit_column): ?>
                <?php list($model, $field) = parse_edit_column($model_name, $edit_column) ?>
                <th>
                    <?php echo __('model.'.strtolower($model).'.'.$field); ?>
                </th>
            <?php endforeach ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach($models->find_all() as $i => $edit_element): ?>
            <tr data-id="<?php echo $edit_element->pk() ?>">
                <td><?php echo $i+1 ?></td>

                <?php foreach($shown_columns as $field): ?>
                    <?php if(method_exists($edit_element, $field)): ?>
                        <td><?php echo $edit_element->{$field}() ?></td>
                    <?php else: ?>
                        <td><?php echo $edit_element->{$field} ?></td>
                    <?php endif ?>
                <?php endforeach ?>

                <?php foreach($edit_columns as $edit_column): ?>
                    <?php
                    list($model, $field) = parse_edit_column($edit_element, $edit_column);
                    $value = $model->{$field};
                    ?>
                    <td>
                        <div class="input-group">
                            <input onblur="mass_edit(this, <?php echo $edit_element->pk() ?>, <?php echo htmlentities(json_encode($edit_column)) ?>)" class="form-control" value="<?php echo $value ?>" />
                            <span class="input-group-btn">
                                <button class="input-untouched btn btn-default disabled">
                                    <i class="glyphicon glyphicon-ok"></i>
                                </button>
                                <button onclick="revert_edit(this, <?php echo htmlentities(json_encode($value)) ?>)" class="restore-default btn btn-warning" style="display: none">
                                    <i class="fa fa-undo"></i>
                                </button>
                            </span>
                        </div>
                    </td>
                <?php endforeach ?>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>

<script>
    function mass_edit(input, id, column) {
        input = $(input);
        var value = input.val();

        $.post('/admin/mass_edit/<?php echo $profile ?>', {
            id: id,
            column: column,
            value: value
        }).done(function(data) {
            console.log(data);
        }).fail(function(err) {
            gloerr = err.responseText;
        });

        // Enable "restore previous value"
        $('.input-untouched', input.parent()).hide();
        $('.restore-default', input.parent()).show();
    }

    function revert_edit(btn, value) {
        var parent = $(btn).closest('.input-group');

        $('input', parent).val(value).blur();
        $('.input-untouched', parent).show();
        $('.restore-default', parent).hide();
    }
</script>
