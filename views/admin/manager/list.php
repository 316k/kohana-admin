<h2>Gestion des modèles <em><?php echo $model_name ?></em></h2>

<table class="table table-hover">
    <thead>
        <tr>
            <th>Nom</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($models as $model): ?>
            <tr>
                <td><?php echo $model ?></td>
                <td class="text-right">
                    <a class="btn btn-primary" href="/admin/edit/<?php echo $model_name.'/'.$model->pk() ?>"><i class="glyphicon glyphicon-edit"></i></a>
                    <a class="btn btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer cet élément ?')" href="/admin/delete/<?php echo $model_name.'/'.$model->pk() ?>"><i class="glyphicon glyphicon-remove"></i></a>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>

<a href="/admin/edit/<?php echo $model_name ?>" class="btn btn-success" target="_blank"><i class="glyphicon glyphicon-plus"></i> Ajouter</a>
