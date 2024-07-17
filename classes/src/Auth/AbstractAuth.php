<?php

namespace classes\src\Auth;
use classes\src\AbstractCrudObject;
use classes\src\Media\Utilities\Util;

abstract class AbstractAuth {

    protected const AuthTypes = array("instagram", "facebook");
    protected static ?Util $util = null;
    protected int $requestingUsersAccessLevel = 0;
    protected static int $accessLevel = 0;
    public AbstractCrudObject $crud;


    function __construct(AbstractCrudObject $crud) {
        self::$util = new Util();
        $this->crud = $crud;
        if(isset($_SESSION["access_level"])) $this->requestingUsersAccessLevel = $_SESSION["access_level"];
    }

    abstract public  function initiateIntegration(array $args): ?array;
    abstract public  function exchangeToLongLivedToken(string $code, int|string $expiresIn = 0, string $redirect_uri = ""): ?array;
    abstract public  function checkPermissions($token): bool;
}