<?php
foreach($model->table_columns() as $name => $infos) {

    if($infos['key'] == 'PRI')
        continue;
    
    $key = 'model.'.$model->object_name().'.'.$name;
    $translation = __($key) == $key ? '' : __($key);
    echo "    '".$key."' => \"".str_replace('"', '\"', HTML::entities($translation)).'",'."\n";
    if($infos['data_type'] == 'enum') {
        $keys = $model->enum_field_values($name);
        $options = array();
        
        foreach($keys as $key) {
            $key = 'model.'.$model->object_name().'.'.$name.'.'.$key;
            $translation = __($key) == $key ? '' : __($key);
            echo "    '".$key."' => \"".str_replace('"', '\"', HTML::entities($translation)).'",'."\n";     
        }
    }
}
