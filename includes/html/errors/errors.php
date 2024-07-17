<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT")) exit;

$pageTitle = "Errors";
?>
<script> var pageTitle = <?=json_encode($pageTitle)?>; </script>
<div class="page-content" data-page="errors">
    <div class="row" id="dataContainer">
        <div class="col-sm-12">
            <div class="flex-row-start flex-align-center w-100 mt-2">
                <div class="alert alert-danger eNotice mb-2 hidden" role="alert"></div>
            </div>
            <div class="card border-radius-15px hidden dataParentContainer" data-time-from="" data-time-to="">
                <div class="card-body">
                    <div class="flex-row-between eNoticeAutoLoad" style="min-height: 50px;">
                        <p class="pb-5 font-16 font-weight-bold">Showing a maximum of 5000 entries always</p>
                        <div class="alert alert-danger eNotice mb-2 hidden" role="alert"></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover dataTable prettyTable" id="errorDataTable"
                               data-use-pagination="true" data-page-size="1000" data-page-offset="0" data-page-order="DESC">
                            <thead>
                            <th>Id</th>
                            <th>Time</th>
                            <th>Error code</th>
                            <th>Error message</th>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <div class="mt-2 mb-2 pt-2 flex-row-around flex-align-center border-top">
                            <div class="dataNextPage btn btn-outline-google" data-target-table="errorDataTable" data-target-request="getErrors">
                                Load more
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
