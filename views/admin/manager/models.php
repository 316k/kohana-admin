<h2>Gestion des modèles</h2>

<table class="table table-hover col-xs-12">
    <thead>
        <tr>
            <th>Modèle</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($classes as $class): ?>
            <tr>
                <td><?php echo HTML::anchor('/admin/list/'.$class, $class) ?></td>
                <td class="text-right">
                    <a class="btn btn-success" href="/admin/edit/<?php echo $class ?>">
                        <i class="glyphicon glyphicon-plus"></i>
                    </a><a class="btn btn-primary" href="/admin/list/<?php echo $class ?>">
                        <i class="glyphicon glyphicon-list"></i>
                    </a><a class="btn btn-info" href="/admin/properties/<?php echo $class ?>">
                        <i class="glyphicon glyphicon-user"></i>
                    </a>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
