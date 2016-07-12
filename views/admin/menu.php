<?php
$default_icon = 'glyphicon glyphicon-info-sign';
$hide = Kohana::$config->load('admin.show_more');

if(!isset($active_tab)) {
    $active_tab = Kohana::$config->load('admin.default_tab') ?: Arr::get(array_keys($modules), 0);
}
?>

<ul id="tabs" class="nav nav-tabs" style="margin-bottom: 30px;">
    <?php foreach($modules as $module => $details): ?>
        <li id="menu<?php echo $module ?>"<?php echo $active_tab == $module ? ' class="active"' : '' ?>>
            <?php
            $attributes = array(
                'href' => Arr::get($details, 'href', (isset($external_menu) ? '/admin/board/' : '') . '#tab'.ucfirst($module)),
            );
            
            $href = Arr::get($details, 'href');
            if(!$href && !isset($external_menu)) {
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
    <?php if($hide): ?>
        <li id="menuMore">
            <a href="#!">
                <i class="glyphicon glyphicon glyphicon-plus"></i>
            </a>
        </li>
    <?php endif ?>
</ul>

<?php if($hide): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var hide = <?php echo json_encode($hide) ?>;
            var active_tab = <?php echo json_encode(isset($active_tab) ? $active_tab : '') ?> || window.location.hash.substr(4);

            if(hide.indexOf(active_tab) != -1) {
                $('#menuMore i').removeClass('glyphicon glyphicon-plus').addClass('glyphicon glyphicon-minus');
            } else {
                hide.forEach(function(x) {
                    $('#tabs #menu' + x).hide();
                });
            }
            
            $('#menuMore').click(function() {
                hide.forEach(function(x) {
                    $('#tabs #menu' + x).toggle();
                });
                
                var icon = $('i', this);
                
                if(icon.hasClass('glyphicon glyphicon-plus'))
                    icon.removeClass('glyphicon glyphicon-plus').addClass('glyphicon glyphicon-minus');
                else
                    icon.removeClass('glyphicon glyphicon-minus').addClass('glyphicon glyphicon-plus');
            });
        });
    </script>
<?php endif ?>
