<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT")) exit;
use classes\src\Enum\DesignPaths;
use classes\src\Object\transformer\URL;
?>



<div class="row mt-5 section-xs-bg section-xs">
    <div class="col-12 flex-row-around">

        <div class="row ">
            <div class="col-12 col-lg-7 pt-5 pb-5 pl-3 pr-3 pl-sm-5 pr-sm-5 border-radius-tl-bl-20px border-lg-left border-lg-top border-lg-bottom  border-lg-right-0 border-primary-dark" >
                <div class="flex-col-start">
                    <p class="font-30 font-weight-bold mt-4">Welcome to <?=BRAND_NAME?></p>
                    <p class="font-16 text-gray">Signup as a creator</p>

                    <div class="flex-col-start mt-5" id="user_signup_form">
                        <p class="font-16 ">I am a</p>
                        <select class="form-control mt-1" name="access_level">
                            <?php
                            $crud = new \classes\src\AbstractCrudObject();
                            $availableRoles = $crud->userRoles()->getByX(["name" => ["creator", "brand"]]);
                            foreach ($availableRoles as $role): ?>
                                <option value="<?=$role['access_level']?>"><?=\classes\src\Object\transformer\Titles::clean($role['name'])?></option>
                            <?php endforeach; ?>
                        </select>

                        <p class="font-16 ">Email</p>
                        <input type="text" name="email" placeholder="youemail@example.com" class="form-control mt-1" />

                        <p class="font-16 mt-3">Name</p>
                        <input type="text" name="full_name" placeholder="Your name" class="form-control mt-1" />

                        <p class="font-16 mt-3">Password</p>
                        <div class="position-relative">
                            <input type="password" name="password" placeholder="Your password goes here" class="form-control mt-1 togglePwdVisibilityField" />
                        </div>

                        <p class="font-16 mt-3">Repeat the password</p>
                        <div class="position-relative">
                            <input type="password" name="password_repeat" placeholder="Repeat the password" class="form-control mt-1 togglePwdVisibilityField" />
                        </div>

                        <button class="btn-sec btn-base border-transparent mt-4" name="signup_user">Sign up</button>
                    </div>
                </div>
            </div>

            <div class="d-none d-lg-block col-lg-5 border border-radius-tr-br-20px border-lg-left-0 color-white bg-primary-dark" >
                <div class="flex-col-around h-100">

                    <div class="flex-col-start flex-align-center">
                        <p class="font-22 font-weight-bold text-center">Already have an account?</p>
                        <a href="<?=URL::addParam(HOST, array("page" => "login"), true)?>" class="btn-sec-reversed btn-base mt-4 mxw-150px">Sign in</a>
                    </div>

                </div>
            </div>
        </div>


    </div>
</div>
