<?php $unique = uniqid(); ?>
<div class="panel panel-default search-widget">
    <div class="panel-heading">
        <h3 class="panel-title">
            <i class="glyphicon glyphicon-search"></i>
            <?php echo __('view-admin-filter-title'); ?>
            
            <span style="display: block; width: 100%; text-align: right;">
                <a id="icon<?php echo $unique; ?>" href="javascript:toggle_search_block('#pbody<?php echo $unique; ?>', '#icon<?php echo $unique; ?>');" style="position: absolute; margin-top: -18px; margin-left: -15px;">▲</a>
            </span>
        </h3>
    </div>
    
    <div class="panel-body" id="pbody<?php echo $unique; ?>">
        <?php
        echo __('view-admin-filter-label').' :<br /><br />'.
             Form::input('filter-'.$unique, null, array('class' => 'form-control', 'style' => 'width: 90%; display: inline-block; float: left;'));
        ?>
        
        <a class="btn btn-success" href="#!" style="width: 9%; display: inline-block; float: right;"><i class="glyphicon glyphicon-search"></i></a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        $.expr[':'].containsIgnoreCase = function (n, i, m) {
            return jQuery(n).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
        };
        
        $('input[name=filter-<?php echo $unique; ?>]').change(function() {
            
            
            $('#tab<?php echo ucfirst($model_name); ?> tbody tr').each(function() {
                $(this).show();
                $(this).not(':containsIgnoreCase('+ $('input[name=filter-<?php echo $unique; ?>]').val() +')').hide();
            });
        });
    });
</script>

<br />
