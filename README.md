kohana-admin
============

A flexible admin panel for Kohana.

## Setup

Add it to your project as a submodule :

### Git :

```bash
$ git submodule add https://github.com/316k/kohana-admin.git system
$ git submodule init
$ git commit -m 'Added kohana-admin'
```

### Mercurial (`hg`)

```bash
$ echo "modules/kohana-admin = [git]git://github.com/316k/kohana-admin.git" >> .hgsub
$ hg add .hgsub
$ git clone git://github.com/316k/kohana-admin.git modules/kohana-admin
```

### Anything else

```bash
$ git clone git://github.com/316k/kohana-admin.git modules/kohana-admin
```

## Config

Copy the file `modules/kohana-admin/config/admin.php` to APPPATH/config/admin.php and edit it :

Fields you might want to change :

- `browse_for_fields` : Array of objects => belongs_to relation that should be filled using an external window to find the matching model,
- `ignored_fields` : Array of objects => fields that shouldn't editable from the editor,
- `modules` : Defined below
- `default_tab ` : Name of the module (ModelName) that shoud be shown by default,

Fields you might not touch in simple projects :

- `redirect` : redirection URL after . Note that :model is replaced by the currently edited model name (e.g.: "User")
- `protected_fields` : Array of objects => fields that should not be sent if they aren't modified (e.g.: to avoid automatic rehashing of a field or unnecessarily updating dates)
- `null_value` : A string value that should be interpreted as `NULL`. "{NULL_ON_PURPOSE}" is the default one, and should pretty much do it.

Note the the "Array of objects => field ..." follow this structure :

```PHP
array(
    'object_name' => array('field1', 'field2', ...),
    ...
),
```

Where `object_name` is the lowercase singular name of a Model.

### Modules

Modules follow this structure :

```
    "modules" => array(
        "CapitalizedModelName" => array( // As in ORM::factory('ModelName')
            'icon' => 'glyphicon glyphicon-cog', // Thought for glyphicons or Font-Awesome, but any 'class' value you would put in a <i> tag
            'views' => array('list', ...), // Views to concatenate in the module's tab (see below)
            'list_buttons' => array('add', 'edit', 'delete') // Buttons for the 'list' view (see below)
            'list_fields' => array('__toString', 'name', 'date') // Fields to display in the 'list' view (default : array('__toString'))
        ),
    ),
```

#### Module Views

Predefined views are `filter` and `list`.

`list` is used to list models, and uses `list_buttons` to generate buttons and
display `list_fields` in a table.

A view is simply the name of a Kohana view located in
`views/admin/board/{view name}`. This means you can overwrite views thanks to
Kohana's cascading file system.

The details of a module ("CapitalizedModelName" => *details (array)*) are passed
to the view as $details. Thus, you can do : `Arr::get($details, 'list_buttons')`
to find the 'list_buttons' in the module's details.

#### `list_buttons`

Predefined buttons are 'add', 'edit' and 'delete', but you can add your own :

```
'list_buttons' => array('add', 'edit',
    'label' => array(
        'button' => array(  // HTML::attributes
            'class' => "btn btn-success"
            'data-stuff' => 'attributes',
        ),
        'icon' => 'glyphicon glyphicon-plus',
        'position' => 'side', // "side" will add the button on the side of each line, "bottom" will add it at the end of the models list
    ),
```

Notes :
- `label` will be passed to the `__()` (i18n) function.
- The values ':id' and ':model' values in entries of `button` will be replaced by (respectively) the corresponding `$model->pk()` and the corresponding `ModelName`

## TODO

- [ ] Non-bootstrap template
- [ ] Clean the filter view
- [ ] Find a way not to use this module in the subfolder of a server without
forcing a protocol ('http') with URL::site
- [ ] Document undocumented features
    - [ ] Password reset
    - [ ] filters functions
    - [ ] file upload
    - [ ] fields attributes
