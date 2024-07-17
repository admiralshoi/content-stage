<?php
if(!defined("IN_VIEW") || !defined("ROOT")) exit;
$pageTitle = BRAND_NAME . " - Register";
use classes\src\Object\transformer\URL;
?>
<script> var pageTitle = <?=json_encode($pageTitle)?>; </script>
<div class="landing_top_cover_container">
    <div class="landing_top_cover">
        <div class="row flex-center-items">
            <div class="col-11 col-md-10 col-xl-4">
                <div class="card loginCard">
                    <div class="card-body">
                        <div class="flex-col-start flex-align-start">
                            <p class="font-18"><?=BRAND_NAME?> Register</p>
                            <a href="<?=HOST?>" class="color-blue">Sign in</a>
                        </div>


                        <form method="post" action="">

                            <div class="row">
                                <input type="text" name="username" aria-label="username" placeholder="Username" class="form-control"/>
                            </div>

                            <div class="row">
                                <input type="text" name="nickname" aria-label="nickname" placeholder="Full name" class="form-control"/>
                            </div>

                            <div class="row">
                                <input type="email" name="email" aria-label="email" placeholder="Your email" class="form-control"/>
                            </div>
                            <div class="row">
                                <input type="text" name="password" aria-label="password" placeholder="Password" class="form-control"/>
                            </div>
                            <div class="row">
                                <input type="password" name="password_repeat" aria-label="password" placeholder="Repeat password" class="form-control"/>
                            </div>

                            <div class="row button-row">
                                <button class="btn btn-dw" name="register_user">
                                    Register
                                </button>
                            </div>

                            <div class="mt-2">
                                <?php
                                if(isset($_SESSION["error"]["login"]) && !empty($_SESSION["error"]["login"])) {
                                    echo $_SESSION["error"]["login"];
                                    unset($_SESSION["error"]["login"]);
                                    if(count($_SESSION["error"]) === 0)
                                        unset($_SESSION["error"]);
                                }
                                ?>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
