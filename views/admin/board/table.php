<table class="table table-striped">
    <thead>
        <tr>
            <?php foreach($details['list_fields'] as $field): ?>
                <th>
                    <?php echo __('model.' . strtolower($model_name) . '.table.' . $field); ?>
                </th>
            <?php endforeach ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach($models as $element): ?>
            <tr>
                <?php foreach($details['list_fields'] as $field): ?>
                    <td><?php echo Arr::get($element, $field, '-'); ?></td>
                <?php endforeach ?>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
