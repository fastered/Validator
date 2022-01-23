# Простой PHP валидатор (Validator)


## Подключение: 
```php
require_once 'Validator.php';
```

## Пример использования
```php
$post = [
    'name' => 'Username',
    'email' => 'kb625@gmail.com',
    'tel' => '+79566472235',
    'password' => 'password',
    'confirm_password' => 'password',
    'date' => '2022-01-22'
];

$validation = new Validator($post);
```
В валидатор можно передавить сразу массив $_POST или $_GET
```php
$validation = new Validator($_POST);
```
или
```php
$validation = new Validator($_GET);
```
Для проверки уникальности записи, можно использовать метод unique('table'), передав ему имя таблицы вашей базы данных, 
и обязательно передать Валидатору объект PDO:
```php
$pdo = new PDO('mysql:host=YourHost;dbname=YourDBname;charset=utf8', 'username', 'password');
$validation = new Validator($post, $pdo);
```
Для валидации каждого поля нужно вызвать метод field с именем поля, а потом все остальные методы проверки, 
в них вы можете передавать ваше сообщение об ошибке, если не устраивает то что по умолчанию, как в примере:

```php
$validation->field('name')->required();
$validation->field('email')
    ->email('Сделай так чтобы это было похоже на почту')
    ->required('Email не может быть пустой')->unique('users');
$validation->field('password')->min(8)->max(9);
$validation->field('confirm_password')->similar('password');
$validation->field('tel')->tel();
$validation->field('date')->date();

if($validation->all_right()){
    echo'Проверку прошли!';    
}else{
  echo  $validation->show_erors();     
}

```
