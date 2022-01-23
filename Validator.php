<?php
/**
 * Created by PhpStorm.
 * User: Valera
 * Date: 22.01.2022
 * Time: 20:28
 */

class Validator
{
    /**
     * Validator constructor.
     * @param array $post_data - принимает массив с данными, можно передать ему срузу массив пост, или гет;
     * @param null $pdo - также можно передать объект PDO, или задать его внутри класса
     */
    function __construct(array $post_data, $pdo = null)
    {
        $this->values = $post_data;
        if($pdo == null){
            $this->db = new PDO('mysql:host=192.168.1.14;dbname=studies;charset=utf8', 'student', 'Student123123456!');
        }else{
            $this->db = $pdo;
        }

    }

    protected $values;
    protected $field;
    protected $errors = [];
    protected $db;

    /**
     * @param string $table
     * @param string $name_col
     * @param $value
     * @return bool
     */
    protected function not_unique_in_table(string $table, string $name_col, $value)
    {
        $sql = "SELECT * FROM {$table} WHERE  {$name_col} = :{$name_col}";
        $statement = $this->db->prepare($sql);
        $statement->execute([$name_col => $value]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ( empty($row) ) {
            return false;
        }
        return true;
    }


    /**
     * @param string $error - записывает ошибки валидации в массив error
     */
    protected function write_error($error = '')
    {
        $this->errors[]=$error;
    }

    /**
     * @return bool - проверяет если есть проверяемое поле в массиве, возвращает bool
     */
    protected function not_exist_field()
    {
        if (!isset($this->values[$this->field])){
            return true;
        }
        return false;
    }

    /**
     * @param $string - записывает поле в переменную field и проверяется если есть в проверяемом массиве, записывает ошибку если есть
     * @return $this
     */
    public function field($string)
    {
        $this->field = $string;

        if($this->not_exist_field()){
            $this->write_error('Поле "'.$this->field.'" не существует');
        }

        return $this;
    }


    /**
     * @return bool - проверяет наличие ошибок, используется для определения прохождение валидации
     */
    public function all_right()
    {
        if(empty($this->errors)){
            return true;
        }
        return false;
    }

    /**
     * @return string - возвращает ошибки в текстовом виде, если они есть
     */
    public function show_erors(){
        $text ='';
        foreach ($this->errors as $error) {
            $text.=$error.'<br>';
        }
        return $text;
    }

    /** Проверяет поле на пустую строку, если так, записывает сообшение об ошибке
     * @param string $other_error_message - можно задать свой текст ошибки, если не устраивает по умолчанию
     * @return $this
     */
    public function required($other_error_message = '')
    {
        if ($this->not_exist_field())
            return $this;
        if($this->values[$this->field] == ''){
            if(empty($other_error_message)){
                $this->write_error('Поле "'.$this->field.'" не может пустым!');
            }else{
                $this->write_error($other_error_message);
            }

        }
        return $this;
    }

    /** Проверяет на соответсвие стандарту типа Email
     * @param string $other_error_message - можно задать свой текст ошибки, если не устраивает по умолчанию
     * @return $this
     */
    public function email($other_error_message = '')
    {
        if ($this->not_exist_field() or $this->values[$this->field]=='')
            return $this;

        if(!filter_var($this->values[$this->field],FILTER_VALIDATE_EMAIL)){
            if(empty($other_error_message)){
                $this->write_error('Поле "'.$this->field.'" должен быть емаил');
            }else{
                $this->write_error($other_error_message);
            }

        }
        return $this;
    }

    /** Проверяет на уникальность записи в базе данных
     * @param $table - имя таблице откуда ищем запись
     * @param string $other_error_message - можно задать свой текст ошибки, если не устраивает по умолчанию
     * @return $this
     */
    public function unique($table, $other_error_message = '')
    {
        if ($this->not_exist_field() or $this->values[$this->field]=='')
            return $this;

        if($this->not_unique_in_table($table,$this->field,"{$this->values[$this->field]}")){
            if(empty($other_error_message)){
                $this->write_error("{$this->field} \"{$this->values[$this->field]}\" уже занят");
            }else{
                $this->write_error($other_error_message);
            }

        }
        return $this;
    }

    /** Проверяет чтобы длина записи была не меньше заданной
     * @param int $min_len  - минимальное количество символов
     * @param string $other_error_message - можно задать свой текст ошибки, если не устраивает по умолчанию
     * @return $this
     */
    public function min(int $min_len = 0, $other_error_message = '')
    {
        if ($this->not_exist_field())
            return $this;

        if(strlen($this->values[$this->field]) < $min_len){
            if(empty($other_error_message)){
                $this->write_error("Минимальное количество символов в поле {$this->field}, должно быть \"{$min_len}\" ");
            }else{
                $this->write_error($other_error_message);
            }

        }
        return $this;
    }

    /** Проверяет чтобы длина записи была не больше заданной
     * @param int $max_len - максимальное количество символов, по умольчанию 10
     * @param string $other_error_message - можно задать свой текст ошибки, если не устраивает по умолчанию
     * @return $this
     */
    public function max(int $max_len = 10, $other_error_message = '')
    {
        if ($this->not_exist_field())
            return $this;

        if(strlen($this->values[$this->field]) > $max_len){
            if(empty($other_error_message)){
                $this->write_error("Максимальное количество символов в поле {$this->field}, должно быть \"{$max_len}\" ");
            }else{
                $this->write_error($other_error_message);
            }

        }
        return $this;
    }

    /** Проверяет чтобы запись была одинаковой как в заданном поле, в нашем случае проверяет похож он на password
     * @param string $password - имя поле с основным паролем
     * @param string $other_error_message - можно задать свой текст ошибки, если не устраивает по умолчанию
     * @return $this
     */
    public function similar(string $password, $other_error_message = '')
            {
                if ($this->not_exist_field())
                    return $this;

                if($this->values[$password] !== $this->values[$this->field] ){
                    if(empty($other_error_message)){
                        $this->write_error(" {$this->field}, должено быть одинаковым с \"{$password}\" ");
                    }else{
                        $this->write_error($other_error_message);
                    }

                }
                return $this;
        }

    /** Проверяет является ли запись номером телефона, в международном формате
     * @param string $other_error_message - можно задать свой текст ошибки, если не устраивает по умолчанию
     * @return $this
     */
    public function tel($other_error_message = '')
            {
                if ($this->not_exist_field())
                    return $this;

                if(!preg_match("/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/", $this->values[$this->field])){
                    if(empty($other_error_message)){
                        $this->write_error(" {$this->field}, должено быть похож на номер телефона");
                    }else{
                        $this->write_error($other_error_message);
                    }

                }
                return $this;
        }

    /** Проверяет является ли запись датой, в формате YYYY-MM-dd
     * @param string $other_error_message - можно задать свой текст ошибки, если не устраивает по умолчанию
     * @return $this
     */
    public function date($other_error_message = '')
            {
                if ($this->not_exist_field())
                    return $this;

                if(!preg_match("/^(19|20)\d\d-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/", $this->values[$this->field])){
                    if(empty($other_error_message)){
                        $this->write_error(" {$this->field}, должен быть датой");
                    }else{
                        $this->write_error($other_error_message);
                    }

                }
                return $this;
        }





}