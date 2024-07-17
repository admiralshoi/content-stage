<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT")) exit;
use classes\src\Enum\IncludePaths;
use classes\src\AbstractCrudObject;

$crud = new AbstractCrudObject();
$targetPage = null;

if(isset($_GET["t"])) {
    $targetPage = match ($_GET["t"]) {
        default => null,
        "pp" => "privacy_policy",
        "tou" => "terms_of_use",
    };
}


$policyPath = $crud->appMeta()->get("current_$targetPage");
$policyPath = is_file(ROOT . $policyPath) && file_exists(ROOT . $policyPath) ? $policyPath : IncludePaths::privacy_policy_default;




$pageTitle = "Edit page";

ob_start();
include_once ROOT . $policyPath;
$policyContent = ob_get_clean();



?>

<script>
    var pageTitle = <?=json_encode($pageTitle)?>;
</script>


<div class="page-content" data-page="edit_page">

    <div class="row">
        <div class="col-12 mt-5">
            <?php if(is_null($targetPage)): ?>
                <p class="font-25 font-weight-bold">
                    No page to edit found
                </p>
            <?php else: ?>

                <p class="font-30 text-center">
                    Edit <?=\classes\src\Object\transformer\Titles::cleanUcAll($targetPage)?>
                </p>

                <div class="richText-toolbar">
                    <div class="flex-row-between flex-align-center">
                        <ul class="tool-list">
                            <li class="tool">
                                <button
                                        type="button"
                                        data-command='justifyLeft'
                                        class="tool--btn">
                                    <i class='mdi mdi-format-align-left'></i>
                                </button>
                            </li>
                            <li class="tool">
                                <button
                                        type="button"
                                        data-command='justifyCenter'
                                        class="tool--btn">
                                    <i class='mdi mdi-format-align-center'></i>
                                </button>
                            </li>
                            <li class="tool">
                                <button
                                        type="button"
                                        data-command="bold"
                                        class="tool--btn">
                                    <i class='mdi mdi-format-bold'></i>
                                </button>
                            </li>
                            <li class="tool">
                                <button
                                        type="button"
                                        data-command="italic"
                                        class="tool--btn">
                                    <i class='mdi mdi-format-italic'></i>
                                </button>
                            </li>
                            <li class="tool">
                                <button
                                        type="button"
                                        data-command="underline"
                                        class="tool--btn">
                                    <i class='mdi mdi-format-underline'></i>
                                </button>
                            </li>
                            <li class="tool">
                                <button
                                        type="button"
                                        data-command="insertOrderedList"
                                        class="tool--btn">
                                    <i class='mdi mdi-format-list-bulleted-type'></i>
                                </button>
                            </li>
                            <li class="tool">
                                <button
                                        type="button"
                                        data-command="insertUnorderedList"
                                        class="tool--btn">
                                    <i class='mdi mdi-format-list-bulleted'></i>
                                </button>
                            </li>
                            <li class="tool">
                                <button
                                        type="button"
                                        data-command="createlink"
                                        class="tool--btn">
                                    <i class='mdi mdi-link'></i>
                                </button>
                            </li>

                            <li class="tool flex-col-around ml-4 ">
                                <p class="font-16 text-gray">Select text to format it</p>
                            </li>
                        </ul>

                        <button class="btn btn-green-white mr-5" name="update_page_edit" data-target-name="<?=$targetPage?>">Update page</button>
                    </div>

                </div>
                <div id="richTextOutput" contenteditable="true"><?=$policyContent?></div>



            <?php endif; ?>
        </div>
    </div>
</div>