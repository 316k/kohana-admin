<h2>Valeurs utilisables pour les gabarits avec le modèle <em><?php echo $model_name ?></em></h2>

<?php
if(!$element->loaded()) {
    $element = ORM::factory($model_name)->order_by($element->primary_key(), 'DESC')->find();
}

$properties = array();

foreach($element->list_columns() as $column => $details) {
    $properties[$column] = $element->$column;
}

$methods = array_diff(get_class_methods($element), get_class_methods('ORM'));

foreach($methods as $method) {
    $reflection = new ReflectionMethod('Model_'.$model_name, $method);
    if($reflection->getNumberOfRequiredParameters() === 0) {
        $properties[$method.'()'] = (string) $element->$method();
    }
}
?>

<a href="/admin/properties/<?php echo $model_name.'/'.ORM::factory($model_name)->order_by(DB::expr('RAND()'))->find()->pk() ?>">
    Nouvelles valeurs d'exemple
</a>

<table class="table table-hover">
    <thead>
        <tr>
            <th>Clé</th>
            <th>Valeur d'exemple</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($properties as $property => $value): ?>
            <tr>
                <td>{<?php echo $model_name ?>|<?php echo $property ?>}</td>
                <td><?php echo $value ?></td>
                <?php $description = 'model.'.strtolower($model_name).'.'.$property ?>
                <td><?php echo __($description) === $description ? '-' : __($description) ?></td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
