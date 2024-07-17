<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT"))
    exit;

$pageTitle = "Activity log";
?>
<script> var pageTitle = <?=json_encode($pageTitle)?>; </script>

<div class="page-content">
    <div class="row">
        <div class="col-sm-12" id="dataContainer" >
            <div class="flex-row-start flex-align-center w-100 mt-2">
                <div class="alert alert-danger eNotice mb-2 hidden" role="alert"></div>
            </div>
            <div class="card border-radius-15px dataParentContainer">
                <div class="card-body">
                    <div class="flex-row-between eNoticeAutoLoad" style="min-height: 50px;">
                        <div class="flex-col-start pb-5">
                            <p class="font-16 font-weight-bold">Activity log</p>
                        </div>
                        <div class="alert alert-danger eNotice mb-2 hidden" role="alert"></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover dataTable prettyTable" id="activityLogTable"
                            data-use-pagination="true" data-page-size="1000" data-page-offset="0" data-page-order="DESC">
                            <thead>
                            <th>Id</th>
                            <th>Date / Time ao</th>
                            <th>Action</th>
                            <th>Name</th>
                            <th>Employee</th>
                            </thead>
                            <tbody></tbody>
                        </table>

                        <div class="mt-2 mb-2 pt-2 flex-row-around flex-align-center border-top">
                            <div class="dataNextPage btn btn-outline-google" data-target-table="activityLogTable" data-target-request="activityDataList">
                                Load more
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
