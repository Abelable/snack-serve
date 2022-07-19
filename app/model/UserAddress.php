<?php

namespace app\model;

class UserAddress extends BaseModel
{
    protected $hidden =['id', 'user_id', 'update_time', 'delete_time'];
}