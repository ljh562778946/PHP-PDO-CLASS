## Initialize class and Declare object

    include_once 'database.class.php';
    //host, username, password, database, mysql port
    $db = new Database('localhost', 'root', 'password', 'database', 3306);

## Insert
   
    $array = array(
        'username' => 'example',
        'password' => md5('password'),
        'email' => 'example@example.com'
    );

    $db->_insert('users', $array);

## Select, order, limit, where
     
### Order

     $db->_order('user_id', true); // True for descending, false for ascending

### Limit

    $db->_limit(1);

### Where

    $db->_where('user_id', '=', '1'); // field, operator, value

### Select

    $db->_select('users');

> order, limit and where functions must come before the select function, you can use multiple instances of the where function.

Example:

    $db->_where('user_id', '>=', '1');
    $db->_where('user_id', '<=', '100');
    $db->_select('users');

> To return the results refer to the results function below.

## Update

    $array = array(
        'email' => 'newemail@email.com'
    );

    $db->_update('users', $array);

> Update works the same as the select function, the where instance must be declared before the update function

## Delete

    $db->_delete('users');

> Delete works the same as the select function, the where instance must be declared before the delete function

## Results

    $db->_results();

## Row Count

    $db->_rows();

## Last Insert ID

    $db->_last_insert_id();

## Begin Transaction

    $db->_beginTransaction();

## End Transaction

    $db->_endTransaction();

## Cancel Transaction (Roll Back)

    $db->_cancelTransaction();

> If you have questions regarding this class please leave an issue and I will address asap.

    
