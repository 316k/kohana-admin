<?php
$default_icon = 'glyphicon glyphicon-info-sign';
$default_tab = Kohana::$config->load('admin.default_tab') ?: Arr::get(array_keys($modules), 0);
?>

<ul id="tabs" class="nav nav-tabs" style="margin-bottom: 30px;">
    <?php foreach($modules as $module => $details): ?>
        <li id="menu<?php echo $module ?>"<?php echo $default_tab == $module ? ' class="active"' : '' ?>>
            <?php
            $attributes = array(
                'href' => Arr::get($details, 'href', '#tab'.ucfirst($module)),
            );

            $href = Arr::get($details, 'href');
            if($href && $href != '#!') {
                $attributes['target'] = '_blank';
            } else if(!$href) {
                $attributes['data-toggle'] = 'tab';
            }
            ?>
            <a<?php echo HTML::attributes($attributes) ?>>
                <i class="<?php echo Arr::get($details, 'icon', $default_icon) ?>"></i>
                
                <?php $label = __('admin-board-'.strtolower($module)) ?>
                <?php if($label): ?>
                    <span class="hidden-xs hidden-print" style="margin-left: 10px;">
                        <?php echo $label ?>
                    </span>
                <?php endif ?>
            </a>
        </li>
    <?php endforeach ?>
</ul>

<div class="tab-content">
    <?php foreach($modules as $module => $details): ?>
        <?php
        if(Arr::get($details, 'href', '#!') != '#!') {
            continue;
        }

        $models = Arr::get($details, 'models', FALSE);
        $model_name = Arr::get($details, 'model_name', $module);

        if($models === FALSE) {
            $models = ORM::factory($model_name);
        }
        ?>
        <div class="tab-pane<?php echo $default_tab == $module ? ' active' : '' ?>" id="tab<?php echo ucfirst($module); ?>">
            <?php
            foreach(Arr::get($details, 'views', array()) as $view) {
                echo View::factory('admin/board/'.$view, array(
                    'model_name' => $model_name,
                    'details' => $details,
                    'models' => $models
                ));
            }
            ?>
        </div>
    <?php endforeach ?>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#tabs a').click(function() {
        window.location.hash = $(this).attr('href');
    });
    
    $('#tabs a[href="' + window.location.hash + '"]').tab('show');
});

function confirm_delete(model, id) {
    if(confirm("Voulez-vous vraiement supprimer l'élément sélectionné ?")) {
        window.location = '<?php echo URL::site("/admin/delete", "http"); ?>/' + model + '/' + id;
    }
}
</script>
