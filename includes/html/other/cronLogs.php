<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT")) exit;

use classes\src\AbstractCrudObject;
use classes\src\Object\transformer\URL;
use classes\src\Auth\Auth;
use classes\src\Object\objects\Integrations;
$crud = new AbstractCrudObject();

$pageTitle = "Cronjob logs";






?>
    <script>
        var pageTitle = <?=json_encode($pageTitle)?>;
    </script>
    <div class="page-content position-relative" data-page="cron-jobs">

        <div class="flex-row-start flex-align-center font-22 font-weight-medium">
            <p>Cron logs</p>
        </div>


        <div class="row mt-5">
            <div class="col-sm-10 m-auto">
                <div class="card">
                    <div class="card-body">
                        <div class="table">
                            <table>
                                <thead>
                                <th>Name</th>
                                <th>Link</th>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>Hashtag Tracking</td>
                                    <td><a href="<?=HOST?>cronjobs/cronLog.php?job=HASHTAG_TRACKING" target="_blank">Open</a></td>
                                </tr>
                                <tr>
                                    <td>Campaign Media Updates</td>
                                    <td><a href="<?=HOST?>cronjobs/cronLog.php?job=MEDIA_UPDATE" target="_blank">Open</a></td>
                                </tr>
                                <tr>
                                    <td>Tag Mentions</td>
                                    <td><a href="<?=HOST?>cronjobs/cronLog.php?job=TAG_MENTION" target="_blank">Open</a></td>
                                </tr>
                                <tr>
                                    <td>Account Analytics</td>
                                    <td><a href="<?=HOST?>cronjobs/cronLog.php?job=ACCOUNT_INSIGHTS" target="_blank">Open</a></td>
                                </tr>
                                <tr>
                                    <td>Error logs</td>
                                    <td><a href="<?=HOST?>cronjobs/cronLog.php?job=ERROR_LOG" target="_blank">Open</a></td>
                                </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>






    </div>

<?php
$crud->closeConnection();