<?php
if(!defined("IN_VIEW") || !defined("ROOT")) exit;
$pageTitle = "FAQ";
use classes\src\AbstractCrudObject;
use classes\src\Object\transformer\Titles;

$crud = new AbstractCrudObject();
$appMeta = $crud->appMeta();


?>
<script> var pageTitle = <?=json_encode($pageTitle)?>; </script>
<div class="main-wrapper m-0 ">
    <div class="row mt-5 mb-5 pt-2 flex-row-around w-100">
        <div class="col-sm-12 col-lg-8">
            <div class="card border-radius-15px">
                <div class="card-body">
                    <p class="flex-row-around font-25 font-weight-bold ">FAQ</p>
                    <div class="font-16 mt-2">


<!-- ---------------------------------------------------- PAYOUTS ------------------------------------------------------------------------ -->

                        <div class="mt-4 border-bottom pb-2">
                            <div class="">
                                <p class="font-weight-bold font-italic font-18">
                                    Blaaa
                                </p>
                            </div>

                            <div class="blog-section-content">
                                <div class="title-box">
                                    <p class="font-weight-bold font-25 title-box-header">
                                        How ....
                                    </p>
                                    <div class="title-box-content">
                                        <p>
                                            You dsdasdsa
                                        </p>
                                        <p>
                                            Lorem
                                        </p>
                                    </div>
                                </div>

                                <div class="title-box">
                                    <p class="font-weight-bold font-25 title-box-header">
                                        How is his
                                    </p>
                                    <div class="title-box-content">
                                        <ol>
                                            <li>
                                                Go to <a href="<?=HOST?>?page=general_settings" >'Manage Profile'</a>
                                            </li>
                                            <li>
                                                Look for 'email email'
                                            </li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>