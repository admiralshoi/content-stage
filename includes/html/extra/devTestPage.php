<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT")) exit;

use classes\src\AbstractCrudObject;
use classes\src\Object\transformer\Titles;
use JetBrains\PhpStorm\ArrayShape;
use classes\src\media\Facebook;


//$payment = (new AbstractCrudObject())->payments();
//
//$payment->prepareNewPaymentPeriod();




//use classes\src\Object\RegisterUser;
//
//$crud = new AbstractCrudObject();
//$teamHandler = $crud->teams();
//$employeeHandler = $crud->employee();
//$teams = $teamHandler->getByX();
//
//$existingEmployees = $employeeHandler->getByX();
//$existingNames = array_map(function ($item) { return $item["username"]; }, $existingEmployees);
//
//
//$teamFilter = array_map(function ($team) {
//    return array(
//        "id" => $team["id"],
//        "name" => trim(str_replace("team", "",strtolower($team["name"])))
//    );
//}, $teams);
//
//$teams = array();
//foreach ($teamFilter as $team) $teams[ ($team["id"]) ] = $team["name"];
////echo json_encode($teams);
//
//
//$filename = "bulk_after_test.csv";
////$filename = "bulk_sheet_commenters.csv";
//$path = ROOT . "testing/$filename";
//
//$file = fopen($path, 'r'); $employees = array();
//while (($employee = fgetcsv($file)) !== FALSE) {
//    //$line is an array of the csv elements
//    $employees[] = $employee;
//}
//fclose($file);
//
//$employees = array_map(function ($employee) {
//    return array(
//        "username" => $employee[0],
//        "nickname" => $employee[1],
//        "slack_email" => $employee[2],
//        "paypal_email" => $employee[3],
//        "password" => 123456,
//        "password_repeat" => 123456,
//        "access_level" => 1,
//        "created_by" => 1,
//        "team" => trim(str_replace("team", "", strtolower($employee[4])))
//    );
//}, $employees);
//
//$fields = $errors = array(); $success = 0;
//
//$employeesNotToCreate = array_filter($employees, function ($employee) {
//   return empty($employee["team"]);
//});
//file_put_contents(ROOT . "testing/employeesForLater.json", json_encode(array_values($employeesNotToCreate),JSON_PRETTY_PRINT));
//
//$employees = array_filter($employees, function ($employee) {
//   return !empty($employee["team"]);
//});
//
//foreach ($employees as $i => $employee) {
//    $teamName = $employee["team"] === "lizamae" ? "lizamie" : $employee["team"];
//    $teamId = array_search($teamName, $teams);
////    echo ($teamId === false ? json_encode($employee) : $teamId)."<br>";
////    continue;
////
////    unset($employee["team"]);
//
//    $employee["username"] = str_replace(" ", "", $employee["username"]);
//    $employee["username"] = str_replace("@", "", $employee["username"]);
//    $employee["username"] = trim($employee["username"]);
//
//    if(in_array($employee["username"], $existingNames)) {
//        $success += 1;
//        continue;
//    }
//
//    $register = new RegisterUser($crud);
//    $create = $register->execute($employee);
//
//    if(!empty($register->error)) {
//        $errors[] = array(
//            "error" => $register->error,
//            "employee" => $employee
//        );
//        continue;
//    }
//    elseif (gettype($create) === "string") {
//        $errors[] = array(
//            "error" => $create,
//            "employee" => $employee
//        );
//        continue;
//    }
//
//    $getEmployee = $crud->retrieve("user", array("username" => $employee["username"]));
//    if(empty($getEmployee)) {
//        $errors[] = array(
//            "error" => "Failed to get employee from DB",
//            "employee" => $employee
//        );
//        continue;
//    }
//
//    $employeeId = $getEmployee[0]["id"];
//    $teamParams = array("fields" => array("obj_id" => $teamId, "employee_id" => $employeeId));
//
//    $addTeamMember = json_decode($teamHandler->addMember($teamParams), true);
//    if(array_key_exists("error", $addTeamMember)) {
//        $errors[] = array(
//            "error" => "Failed to create team.   -   " . $addTeamMember["error"],
//            "employee" => $employee
//        );
//        continue;
//    }
//
//    $success += 1;
//}
//
//echo $success . " /" . count($employees) . " successes <br>";
//
//if(!empty($errors)) {
//    echo count($errors) . " errors!!!<br>";
//    echo json_encode($errors);
//
//
//    file_put_contents(ROOT . "testing/errorEmployees.json", json_encode(array_values($errors),JSON_PRETTY_PRINT));
//}
//
//



//foreach ($employees as $i => $employee) {
//
//    foreach ($employee as $field => $value) {
//        if(!array_key_exists($field, $fields)) $fields[$field] = array();
//
//        $fields[$field][] = $value;
//    }
//}
//
//$unique = array(
//    "username" => array_unique($fields["username"]),
//    "slack_email" => array_unique($fields["slack_email"]),
//    "paypal_email" => array_unique($fields["paypal_email"])
//);
//
//foreach ($unique as $field => $list) {
//
//    if($list !== $fields[$field]) {
//        echo json_encode(array_diff($list, $fields[$field])) . "<br><br><br><br>";
//
////        echo $field . "<br>";
//    }
//
//}



/*
 * SCRIPT TO DELETE ALL TRACKS OF WEBHOOK DATA
 */

//use classes\src\AbstractCrudObject;

//$logPath = ROOT . "logs/";
//if(is_dir($logPath)) echo $crud->removeDirectory($logPath, false) ? "deleted-" : "failed-";

//CLEAN accounts
//foreach ($crud->retrieve("account",array()) as $account) {
//    $id = $account["instagram_id"];
//    echo $crud->igAccount()->deleteAccount(array("id"=>$id)) . "<br>";
//}





function deletePlatformDataAll() {
    ini_set('max_execution_time', '-1');
    set_time_limit(-1);

    $crud = new AbstractCrudObject();

    $dirs = array("rouge_users", "raw_saves");
    foreach ($dirs as $dir) {
        $path = STORAGE . "$dir/";
        if(is_dir($path)) {
            echo $crud->removeDirectory($path, false) ? "deleted-" : "failed-";
        }
    }
    $records = ROOT . RECORDS_DIR . OBJ_ENTRIES_DIR;
    if(is_dir($records)) echo $crud->removeDirectory($records, false) ? "deleted-" : "failed-";
    $payments = ROOT . PAYMENT_DIR;
    if(is_dir($payments)) echo $crud->removeDirectory($payments, false) ? "deleted-" : "failed-";
    $accPath = ROOT . ACCOUNTS_DIR;
    $accDirs = $crud->grabDirContent($accPath, true);
    if(!empty($accDirs)) {
        foreach ($accDirs as $accDir) {
            $accEntryDir = $accPath . $accDir . "/" . OBJ_ENTRIES_DIR;
            if(is_dir($accEntryDir)) echo $crud->removeDirectory($accEntryDir, false) ? "deleted-" : "failed-";
        }
    }
    $updateRowsParam = array(
        "total" => 0
    );
    $userRowUpdateParam = array(
        "total" => 0,
        "payment_period_transfer" => 0,
        "pending_payment" => 0,
        "approved_payment" => 0,
        "total_paid" => 0
    );

    $crud->update("account",array_keys($updateRowsParam),$updateRowsParam, array("integrated_by_id" => 3));
    $crud->update("account",array_keys($updateRowsParam),$updateRowsParam, array("integrated_by_id" => 2));
    $crud->update("user",array(),array(), array(),
        "UPDATE users SET total=0, payment_period_transfer=0, pending_payment=0, approved_payment=0, total_paid=0");
    $crud->delete("payment", array(), "DELETE FROM payment");
    $crud->delete("violation", array(), "DELETE FROM violations");
    $crud->delete("error",array(),"DELETE FROM errors");
    $crud->delete("rouge",array(),"DELETE FROM rouge_users");
    $crud->delete("sale",array(),"DELETE FROM sales");


    $employees = $crud->employee()->getByX();

    foreach ($employees as $employee) {
        $dir = $employee["directory"];
        if(file_exists(ROOT . $dir . "/" . USER_BASE_FILE)) {
            $content = json_decode(file_get_contents(ROOT . $dir . "/" . USER_BASE_FILE), true);
            $content["change_log"] = array();
            $content["payment_history"] = array();
            file_put_contents(ROOT . $dir . "/" . USER_BASE_FILE, json_encode($content,JSON_PRETTY_PRINT));

            if (file_exists(ROOT . $dir . "/" . USER_LOGS . USER_CONNECTION_LOG)) {
                unlink(ROOT . $dir . "/" . USER_LOGS . USER_CONNECTION_LOG);
            }
        }
    }


    $crud->closeConnection();

    ini_set('max_execution_time', '150');
    set_time_limit(150);
}

/**
 * Revisit raw-saves and resolve them if possible
 *
 *
 * Order of actions to do:
 * 1: run revisitRawSaves() ( if there's lots, run it through https://balanziapp.com/cronjobs/cronCaller.php?job=AUTOMATED )
 * 2: Run removePastErrors()
 * 3: run revisitRawSaves() with reevaluateRawSaves(true)
 * 4: Disable automatic cronjob
 * 5: run removeReportsAfterRevisitCleanup()
 * 6: run https://balanziapp.com/cronJob_reports.php?token=d7467c0f6198d3ab057d793304d03819 for all daystrings
 * 7: re-enable automatic cronjob
 */
function revisitRawSaves() {
    if(isset($_SESSION["FUCK_OFF"])) return;
    $_SESSION["FUCK_OFF"] = true;
    $crud = new AbstractCrudObject();

    $crud->errorHandler()->reevaluateRawSaves();
    $crud->closeConnection();
}

//revisitRawSaves();
//unset($_SESSION["FUCK_OFF"]);

function removePastErrors(): void {
    $crud = new AbstractCrudObject();

    $errorHandler = $crud->errorHandler();
    $errors = $errorHandler->getByX();

    if(empty($errors)) {
        echo "no errors were found";
        return;
    }

    echo count($errors);
    echo "<br>";

    $collector = $toBeRemoved = array();
    foreach ($errors as $error) {
        $entryPath = $error["target_file"];
        if(file_exists(ROOT . $entryPath)) {

            $key = md5($error["target_file"]);
            if(array_key_exists($key,$collector)) $collector[$key][] = $error;
            else $collector[$key] = array($error);

        }
        else $toBeRemoved[] = $error;
    }

    $doubles = array_filter($collector, function ($list){
        return count($list) > 1;
    });

    $filter = array_map(function ($list) use ($crud) {
        $hold = $list;
        $crud->sortByKey($hold, "id");
        array_pop($hold);
        return $hold;
    }, $doubles);

    $filter = array_values($filter);
    foreach ($filter as $item) {
        $toBeRemoved = array_merge($toBeRemoved, $item);
    }

    $idsToBeRemoved = array_map(function ($item){ return (int)$item["id"]; }, $toBeRemoved);
    $sql = "DELETE FROM errors WHERE id IN (".implode(",",$idsToBeRemoved).")";
//    $crud->delete("error", array(), $sql);
    echo json_encode(array("to_be_removed" => count($toBeRemoved), "doubles" => count($doubles), "ids" => $idsToBeRemoved));




    $crud->closeConnection();
}
//removePastErrors();


function removeReportsAfterRevisitCleanup() {

    ini_set('max_execution_time', '-1');
    set_time_limit(-1);

    $crud = new AbstractCrudObject();
    $reportHandler = $crud->reporting();

    $keyword = "hookDebugSuccess";
    $keywordErr = "hookDebugError";
    $allFiles = $crud->grabDirContent(ROOT . "testLogs/", false, true);

    $files = array_values(array_filter($allFiles, function ($filename) use ($keyword) {
        return str_contains($filename, $keyword);
    }));

    echo count($files) . "<br>";
    $timestamps = $dayStrings = array();

    foreach ($files as $file) {
        if(!file_exists($file)) continue;

        $content = json_decode(file_get_contents($file), true);
        if(empty($content)) continue;

        foreach ($content as $hookResponse) {
            $timestamp = $crud->nestedArray($hookResponse, array("hook_response", "fields", "timestamp"));
            if(!empty($timestamp)) {
//                $timestamps[] = $timestamp;
                $date = date("Ymd", $timestamp);
                if(!in_array($date, $dayStrings)) $dayStrings[] = $date;
            }
        }
    }

    if(empty($dayStrings)) echo "empty";
    else {
        sort($dayStrings);
        $dayStrings = array_unique($dayStrings);
//            $reportHandler->removeReportsByDateStrings($dayStrings);

    }


    $debugFiles = array_values(array_filter($allFiles, function ($filename) use ($keyword, $keywordErr) {
        return str_contains($filename, $keyword) || str_contains($filename, $keywordErr);
    }));

    foreach ($debugFiles as $file) {
        if(file_exists($file)) unlink($file);
    }


    ini_set('max_execution_time', '150');
    set_time_limit(150);

    $crud->closeConnection();
}
//removeReportsAfterRevisitCleanup();





function removeReportsByDateRange() {
    ini_set('max_execution_time', '-1');
    set_time_limit(-1);

    $crud = new AbstractCrudObject();
    $reportHandler = $crud->reporting();

    $dayStrings = array();
    $currentDate = "2022-04-20";
    $dateTo = "2022-04-20";

    while (true) {
        $dayTime = strtotime($currentDate);
        $dayStrings[] = date("Ymd", $dayTime);

        if($currentDate === $dateTo) break;
        $currentDate = date("Y-m-d", strtotime($currentDate . " +1 day"));
    }

//    foreach ($dayStrings as $dayString) echo $dayString . "<br>";
    $reportHandler->removeReportsByDateStrings($dayStrings);



    ini_set('max_execution_time', '150');
    set_time_limit(150);

    $crud->closeConnection();
}
//removeReportsByDateRange();





/**
 * @return bool
 * Force payment period update in app_info
 */
function forceNewPeriod(): void{
    $crud = new AbstractCrudObject();
    echo $crud->app()->updatePeriod($crud->payments()->realPaymentPeriod()) ? "yeaaa" : "no";
    $crud->closeConnection();
}

function prepareNewPaymentPeriod():void { //Same as the one in payment
    $crud = new AbstractCrudObject();
    $paymentHandler = $crud->payments();

    $preparation = $paymentHandler->prepareNewPaymentPeriod(false);
    echo $preparation ? "PRepared!" : "Failed... :/";

    $crud->closeConnection();
}
//prepareNewPaymentPeriod();


function totalEarnings() {
    $crud = new AbstractCrudObject();

    $salesHandler = $crud->sale();
    $employeeHandler = $crud->employee();
    $violationHandler = $crud->violation();

    $timeStart = 1634335200;
    $timeEnd = 1634421600;

    $violationCount = count($violationHandler->getXByTimeInterval($timeStart,$timeEnd));
    $salesOfCount = count($salesHandler->getXByTimeInterval($timeStart,$timeEnd));

//    $employees = $employeeHandler->getByX();
    $employees = $employeeHandler->getXByCreatedAtMax($timeEnd);
    $employeeHandler->disableDepthCheck();
    $costs = array("mention_cost" => 0, "mention_count" => 0, "sales" => 0, "transfer" => 0, "sales_cost" => 0, "sales_count" => 0, "total" => 0, "violations" => $violationCount);
    $emplStat = array();

    if(!empty($employees)) {
        foreach ($employees as $employee) {
            $earnings = $employeeHandler->getEarnings($employee["id"], $timeStart, $timeEnd,"", array(),true);
            $costs["mention_cost"] += $earnings["earnings"];
            $costs["mention_count"] += $earnings["entry_count"];

            $emplStat[] = array(
                "employee_id" => (int)$employee["id"],
                "name" => (int)$employee["nickname"],
                "mention_cost" => $earnings["earnings"],
                "mention_count" => $earnings["entry_count"]
            );


//            $costs["transfer"] += (float)$employee["payment_period_transfer"];
        }
    }

    $costs["sales_cost"] += ($salesOfCount * 2);
    $costs["sales_count"] += $salesOfCount;
    $costs["total"] += $costs["transfer"] + $costs["sales_cost"] + $costs["mention_cost"];


    echo json_encode($costs);

    file_put_contents(ROOT . "testLogs/testingmyown.json", json_encode($emplStat, JSON_PRETTY_PRINT));



    $crud->closeConnection();
}

//totalEarnings();




function periodEarnings(string $periodString): void {
    $crud = new AbstractCrudObject();
    $employeeHandler = $crud->employee();
    $paymentHandler = $crud->payments();
    $salesHandler = $crud->sale();
    $violationHandler = $crud->violation();

    $employeeHandler->disableDepthCheck();


    if(!$paymentHandler->isValidPeriodId($periodString)) {
        echo "not a valid period string";
        return;
    }

    $period = $paymentHandler->periodIdToPeriod($periodString);
    $violationCount = count($violationHandler->getXByTimeInterval($period["start"], $period["end"]));


    $employees = $employeeHandler->getXByCreatedAtMax($period["start"]);
    $collecting = $belowThreshold = array();
    $total = $totalBelow = 0;

    echo count($employees) . "<br><br>";

    foreach ($employees as $employee) {
//        if((int)$employee["id"] !== 251) continue;

        $employeeEarnings = $employeeHandler->getEarnings($employee["id"], 0 ,0, $periodString, array(), true);
        $salesOfPeriod = $salesHandler->salesOfPeriod(array("employee_id" => $employee["id"]), $periodString);

        $earnings = $employeeEarnings["earnings"] + (float)$salesOfPeriod["cost"];
//        $earnings = $employeeEarnings["earnings"] + (float)$employee["payment_period_transfer"] + (float)$salesOfPeriod["cost"];
        $total += $earnings;

        if($earnings > 0 && ($earnings < 5 || (
                empty($employee["paypal_email"]) || !filter_var($employee["paypal_email"], FILTER_VALIDATE_EMAIL) ))) {

            $collecting[] = array(
                "missing_transfer" => $earnings,
                "current_transfer" => (float)$employee["payment_period_transfer"],
                "employee_id" => $employee["id"],
                "nickname" => $employee["nickname"],
                "slack_email" => $employee["slack_email"],
                "paypal_email" => $employee["paypal_email"],
            );

            $totalBelow += $earnings;
//            echo json_encode(array(
//                "total" => $earnings,
//                "mention_earnings" => $employeeEarnings["earnings"],
//                "entry_count" => $employeeEarnings["entry_count"],
//                "transfer" => (float)$employee["payment_period_transfer"],
//                "sales" => (float)$salesOfPeriod["cost"],
//                "employee" => $employee
//            )) . "<br>";

//            $params = array("payment_period_transfer" => $earnings);
//            $belowThreshold[] = array("params" => $params, "identifier" => array("id" => $employee["id"]));

        }
    }

//    file_put_contents(ROOT . "testLogs/employeesWithTransferPrevPeriod.json", json_encode($collecting, JSON_PRETTY_PRINT));

//    if(empty($belowThreshold)) return;
//    echo count($belowThreshold) . "<br><br>";

//    foreach ($belowThreshold as $item) {
//        echo json_encode($item) . "<br>";
//        $employeeHandler->update($item["params"],$item["identifier"]);
//    }

//    echo $total . " - " . $totalBelow . "<br><br>";
    echo json_encode(array("total" => $total, "below" => $totalBelow, "violation_count" => $violationCount));

    $crud->closeConnection();
}
//periodEarnings("20211016-20211023");





function lookupPostLink() {

    $crud = new AbstractCrudObject();
    $recordHandler = $crud->record();

    $entries = $recordHandler->entryByPostLink("https://www.instagram.com/p/CbTQHmOur3l/");
    echo '<div class="text-wrap mxw-600px" style="word-break: break-all; word-wrap: anywhere;">';
    foreach ($entries as $entry) echo json_encode($entry) . "<br><br>";
    echo "</div>";

    $crud->closeConnection();
}
//lookupPostLink();


function wrapTextOutput($str) {
    echo '<div class="text-wrap mxw-600px mt-2 ml-2" style="word-break: break-all; word-wrap: anywhere;">';
    echo is_array($str) ? json_encode($str) : $str;
    echo "</div>";

}


function resetApprovedHiresByTime() {
//    if(isset($_SESSION["FUCK_OFF"])) return;
//    $_SESSION["FUCK_OFF"] = true;

    ini_set('max_execution_time', '-1');
    set_time_limit(-1);

    $crud = new AbstractCrudObject();
    $employeeHandler = $crud->employee();


    $employees = $employeeHandler->getByX(array("access_level" => array(1,4)));
    $totalPoints = array_reduce($employees, function ($initial, $employee) {
        return (!isset($initial) ? 0 : $initial) + (int)$employee["reward_points"];
    });

//    $time = strtotime("2022-03-26");


    $timeFrom = strtotime("2022-03-15");
    $timeTo = strtotime("2022-04-02");


    $counterApproved = $counterRewards = $counterJpHires = $afterHireCounter = $afterRewardCounter =
    $initialHireCount = $initialRewardCount = 0;

    foreach ($employees as $employee) {

        /**
         * Dont enable this one along with approved hires
         */
        if((int)$employee["access_level"] !== 4) continue; // job poster
//        $paidHires = $employeeHandler->getRecruitmentList(0, $employee["directory"], "paid_hires");
//        echo count($paidHires);
//        if(!empty($paidHires)) {
//            $paidHires = array_values(array_filter($paidHires, function ($hire) use ($timeFrom, $timeTo) {
//                return (int)$hire["timestamp"] < $timeFrom && (int)$hire["timestamp"] >= $timeTo;
//            }));
//
//            $employeeHandler->setRecruitmentList($paidHires, "paid_hires", $employee["directory"]);
//        }
//        return;

        $approvedHires = $employeeHandler->getRecruitmentList(0, $employee["directory"], "approved_hires");
        $rewardFile = $employeeHandler->getRewardFile(0, $employee["directory"], "approved_rewards");

        $hiresAfterTime = array_filter($approvedHires, function ($hire) use ($timeFrom, $timeTo) {
            return (int)$hire["timestamp"] >= $timeFrom && (int)$hire["timestamp"] < $timeTo;
        });
        if(empty($hiresAfterTime)) continue;

//        if((int)$employee["id"] === 2259) echo json_encode($approvedHires) . "<br><br>";

        $aCount = count($approvedHires);
        $rCount = count($rewardFile);

        $initialHireCount += $aCount;
        $initialRewardCount += $rCount;

        if((int)$employee["access_level"] === 4) $counterJpHires += count($hiresAfterTime);
        $filterACount = count($hiresAfterTime);
        $counterApproved += $filterACount;


        $hiresAfterTimeKeys = array_keys($hiresAfterTime);
        $hiresAfterTimeKeys = array_reverse($hiresAfterTimeKeys);

        $newHires = array();
        $pointsToDecrease = 0;

        foreach ($hiresAfterTimeKeys as $key) {
            $newHire = $approvedHires[$key];
            unset($approvedHires[$key]);

            $newHire["timestamp"] = $employeeHandler->timeOfCreation($newHire["id"]);
            $newHire["completion"] = 0;

            $newHires[] = $newHire;
        }

        $postACount = count($approvedHires);
        $afterHireCounter += $postACount;

//        $newHires = array_merge(
//            $employeeHandler->getRecruitmentList($employee["id"], $employee["directory"], "new_hires"),
//            $newHires
//        );
//        $employeeHandler->setRecruitmentList($approvedHires, "approved_hires", $employee["directory"]);
//        $employeeHandler->setRecruitmentList($newHires, "new_hires", $employee["directory"]);




        if((int)$employee["access_level"] === 4) continue;


        $rewardsAfterTime = array_filter($rewardFile, function ($rewardItem) use ($timeFrom, $timeTo) {
            return (int)$rewardItem["timestamp"] >= $timeFrom && (int)$rewardItem["timestamp"] < $timeTo && $rewardItem["name"] === "referral";
        });
        $counterRewards += count($rewardsAfterTime);

        if(empty($rewardsAfterTime)) continue;

        $rewardAfterTimeKeys = array_keys($rewardsAfterTime);
        $rewardAfterTimeKeys = array_reverse($rewardAfterTimeKeys);


        foreach ($rewardAfterTimeKeys as $key) {
            $pointsToDecrease += array_key_exists("points", $rewardFile[$key]) ? (int)$rewardFile[$key]["points"] : 0;
            unset($rewardFile[$key]);
        }

        $afterRewardCounter += count($rewardFile);

        echo "$pointsToDecrease<br>";
//        $employeeHandler->rewardPointsDecrease($pointsToDecrease, $employee["id"]);
//        $employeeHandler->setRewardFile($rewardFile, "approved_rewards", $employee["directory"]);

    }

    echo json_encode(array($counterApproved, $counterRewards, $counterJpHires)) . "<br>";
    echo json_encode(array($afterHireCounter, $afterRewardCounter)) . "<br>";
    echo json_encode(array($initialHireCount, $initialRewardCount)) . "<br>";


    ini_set('max_execution_time', '150');
    set_time_limit(150);

    $crud->closeConnection();
}
//resetApprovedHiresByTime();
//unset($_SESSION["FUCK_OFF"]);


function manualReportEdit() {

    ini_set('max_execution_time', '-1');
    set_time_limit(-1);

    $crud = new AbstractCrudObject();
    $reportHandler = $crud->reporting();

    $dayStrings = $reportRows = array();
    $currentDate = "2022-02-27";
    $dateTo = "2022-03-29";

    while (true) {
        $dayTime = strtotime($currentDate);
        $dayStrings[] = date("Y-m-d", $dayTime);

        if($currentDate === $dateTo) break;
        $currentDate = date("Y-m-d", strtotime($currentDate . " +1 day"));
    }

    foreach ($dayStrings as $dayString) {
        $reportRows[] = $reportHandler->getLatestReport(false, (strtotime($dayString . " +1 day") -1));
    }

    $itemCollector = array();

    foreach ($reportRows as $row) {
        if(!file_exists(ROOT . $row["report_file"])) continue;
        $metaFile = json_decode(file_get_contents(ROOT . $row["report_file"]), true);

        if(empty($metaFile) || !array_key_exists("data", $metaFile) || empty($metaFile["data"])) continue;

        foreach ($metaFile["data"] as $objectType => $reportTypeList) {
            if($objectType !== "scraper") continue;

            foreach ($reportTypeList as $reportType => $filename) {
                if(!file_exists(ROOT . $filename)) continue;
                $entryTable = $crud->hashTable($filename);

                $item = $entryTable->getItem("scraper_days_since_post_posted");
                if(empty($item)) continue;

                $chart = $item["chart"];
                $labels = $chart["labels"];
                $series = $chart["series"]["data"];
                $totalCount = $initialTotalCount = $chart["total_count"];

                $lastItem = array_pop($series);
                if($lastItem[0] < 18000) continue;


                $lastLabel = array_pop($labels);
                $totalCount -= $lastItem[2];

                $newTotalCount = array_reduce($series, function($initial, $item) {
                    return (!isset($initial) ? 0 : $initial) + $item[2];
                });

                $item["chart"]["labels"] = $labels;
                $item["chart"]["series"]["data"] = $series;
                $item["chart"]["total_count"] = $newTotalCount;

//                $entryTable->setItem("scraper_days_since_post_posted", $item);
//                $entryTable->save();

                $itemCollector[] = array(
                    "last_item_series" => $lastItem,
                    "last_item_labels" => $lastLabel,
                    "prev_total" => $initialTotalCount,
                    "new_total" => $totalCount,
                    "new_total_calced" => $newTotalCount,
                    "filename = $filename"
                );
            }
        }
    }

    if(!empty($itemCollector)) {
        echo count($itemCollector);
        file_put_contents(ROOT . "testLogs/filetolookat.json", json_encode($itemCollector, JSON_PRETTY_PRINT));
    }

    ini_set('max_execution_time', '150');
    set_time_limit(150);

    $crud->closeConnection();
    return;
}


function recreatePaymentReports() {
    ini_set('max_execution_time', '-1');
    set_time_limit(-1);

    $crud = new AbstractCrudObject();
    $employeeHandler = $crud->employee();
    $paymentHandler = $crud->payments();


    /**
     * Fetch original report as a base file
     * Fectch job poster file too if need be.
     */
    $commenterReportFile = "objects/payments/{PERIOD-DIRECTORY-INSERT-HERE}/report/employees.json";
    $commenterRealReport = json_decode(file_get_contents(ROOT . $commenterReportFile), true);

    /**
     * Change report to what it should be like now
     * in the case below im inserting "personal info"
     *
     * Although that method doesnt exist in balanzi. It's from aricciApp the getPersonalInfoNoAuth().
     */
    $commenterRealReportMapped = array_map(function ($employee) use ($employeeHandler) {
        return array_merge(
            $employee,
//            $employeeHandler->getPersonalInfoNoAuth($employee["id"], true)
        );
    }, $commenterRealReport);


    /**
     * Prepare job poster and employees objects
     * run closePaymentPeriod. Might want to disable the "SESSION check" in the method itself.
     */
    $prepare_reports = array("employees" => $commenterRealReportMapped, "job_posters" => array());
    $res = $paymentHandler->closePaymentPeriod($prepare_reports);
    echo json_encode($res);



    ini_set('max_execution_time', '150');
    set_time_limit(150);

    $crud->closeConnection();
}

function doSomething() {
//    if(isset($_SESSION["FUCK_OFF"])) return;
//    $_SESSION["FUCK_OFF"] = true;


    $crud = new AbstractCrudObject();
    $orderHandler = $crud->orders();
    $userHandler = $crud->user();




    $crud->closeConnection();
    return;




}

doSomething();
//unset($_SESSION["FUCK_OFF"]);


function testNotifications() : void {
    $crud = new AbstractCrudObject();
    $notificationHandler = $crud->notifications();


    $autoViolation = array(
        "type" => "wrong_team",
        "sub_type" => "auto_generated",
        "title" => "Wrong team violation",
        "message" => "The Commenter is not associated with any team connected to the mentioned account <MENTIONED_ACCOUNT>. " .
            "Please change the mentioned username’. This is a part of the violation description",
        "employee_id" => 1,
        "employee_name" => "System Admin",
        "employee_slack_email" => "frederik.admiralshoi@gmail.com",
        "instagram_username" => "myrandomuser",
        "team_id" => 17,
        "team_name" => "team diamond",
        "author_name" => "",
        "author_slack_email" => "",
        "author_id" => "system",
        "generated_by" => "system",
        "permalink" => "https://instagram.com/p/someRandomPostCode",
        "created_at" => time(),
        "read_details" => json_encode(array())
    );


    $newSale = array(
        "type" => "new_sale",
        "sub_type" => "auto_generated",
        "title" => "New sale",
        "message" => "You have just made a new sale, well done!",
        "employee_id" => 1,
        "employee_name" => "System Admin",
        "employee_slack_email" => "frederik.admiralshoi@gmail.com",
        "instagram_username" => "myrandomuser",
        "team_id" => 17,
        "team_name" => "team diamond",
        "author_name" => "",
        "author_slack_email" => "",
        "author_id" => "system",
        "generated_by" => "system",
        "permalink" => "https://instagram.com/p/someRandomPostCode",
        "created_at" => time(),
        "read_details" => json_encode(array())
    );

    $resolvedViolation = array(
        "type" => "resolved_violation",
        "sub_type" => "manually_generated",
        "title" => "Resolved violation",
        "message" => "Good news! One of your violations has been resolved.",
        "employee_id" => 1,
        "employee_name" => "System Admin",
        "employee_slack_email" => "frederik.admiralshoi@gmail.com",
        "instagram_username" => "myrandomuser",
        "team_id" => 17,
        "team_name" => "team diamond",
        "author_name" => "Dejan mr cool",
        "author_slack_email" => "dejan@slackmail.com",
        "author_id" => 2,
        "generated_by" => 2,
        "permalink" => "https://instagram.com/p/someRandomPostCode",
        "created_at" => time(),
        "read_details" => json_encode(array())
    );


    $dailySetupReq = array(
        "type" => "daily_setup_complete",
        "sub_type" => "auto_generated",
        "title" => "Setup required",
        "message" => "You have not yet met the profile setup-requirements. 
                Please make sure that you have claimed at least 10 Instagram-users, have a registered paypal-email as well as that you are a part of a team.",
        "details" => json_encode(array(
            "has_paypal_email" => true,
            "ig_users_greater_than__equal_to_10" => false,
            "has_team" => true
        )),
        "employee_id" => 1,
        "employee_name" => "System Admin",
        "employee_slack_email" => "frederik.admiralshoi@gmail.com",
        "team_id" => 17,
        "team_name" => "team diamond",
        "author_name" => "",
        "author_slack_email" => "",
        "author_id" => "system",
        "generated_by" => "system",
        "created_at" => time(),
        "read_details" => json_encode(array())
    );



//    $universal = array(
//        "send_type" => "universal",
//        "account_type" => "commenter",
//        "title" => "*NOTIFICATION TITLE*",
//        "message" => "*Mike Oxlong*",
//        "slack_email" => "stramajo94@gmail.com",
//        "team_name" => "team destinee",
//        "team_id" => 17,
//        "employee_id" => 1,
//        "notification_id" => 89434903,
//        "notification_language" => "english",
//        "from" => "goldrush_bot",
//    );

    $payload = array(
        array(
            "send_type" => "universal",
            "account_type" => "commenter",
            "title" => "*DIXIE NORMUS*",
            "message" => "*Mike Oxlong*",
            "slack_email" => "stramajo94@gmail.com",
            "team_name" => "team destinee",
            "team_id" => 17,
            "employee_id" => 1,
            "notification_id" => 89434903,
            "notification_language" => "english",
            "from" => "goldrush_bot",
        ),
        array(
            "send_type" => "universal",
            "account_type" => "commenter",
            "title" => "*MIKE OXLONG*",
            "message" => "*Mike Oxlong*",
            "slack_email" => "stramajo94@gmail.com",
            "team_name" => "team destinee",
            "team_id" => 17,
            "employee_id" => 1,
            "notification_id" => 89434903,
            "notification_language" => "english",
            "from" => "bot",
        ),
        array(
            "send_type" => "universal",
            "account_type" => "commenter",
            "title" => "*BLAAA*",
            "message" => "*Mike Oxlong*",
            "slack_email" => "stramajo94@gmail.com",
            "team_name" => "team destinee",
            "team_id" => 17,
            "employee_id" => 1,
            "notification_id" => 89434903,
            "notification_language" => "english",
            "from" => "applicant_bot",
        ),
        array(
            "send_type" => "universal",
            "account_type" => "commenter",
            "title" => "*SOME TITLE*",
            "message" => "*Mike Oxlong*",
            "slack_email" => "stramajo94@gmail.com",
            "team_name" => "team destinee",
            "team_id" => 17,
            "employee_id" => 1,
            "notification_id" => 89434903,
            "notification_language" => "english",
            "from" => "eloisa",
        ),
        array(
            "send_type" => "universal",
            "account_type" => "commenter",
            "title" => "*EIDIT MSOMETHING*",
            "message" => "*Mike Oxlong*",
            "slack_email" => "stramajo94@gmail.com",
            "team_name" => "team destinee",
            "team_id" => 17,
            "employee_id" => 1,
            "notification_id" => 89434903,
            "notification_language" => "english",
            "from" => "manager",
        ),
    );





//    echo json_encode($payload);
//    $payload = array(
//        "send_type" => "universal",
//        "account_type" => "commenter",
//        "title" => "*EIDIT MSOMETHING*",
//        "message" => "*Mike Oxlong*",
//        "slack_email" => "stramajo94@gmail.com",
//        "team_name" => "team destinee",
//        "team_id" => 17,
//        "employee_id" => 1,
//        "notification_id" => 89434903,
//        "notification_language" => "english",
//        "from" => "manager",
//    );
    $package = $notificationHandler->assembleMultiPostParams($payload);
    $notificationHandler->sendV2($package);


//    $notificationHandler->sendNotificationToSlack($payload, true);
//    $notificationHandler->sendNotificationToSlack($autoViolation);
//    $notificationHandler->sendNotificationToSlack($newSale);
//    $notificationHandler->sendNotificationToSlack($resolvedViolation);
//    $notificationHandler->sendNotificationToSlack($dailySetupReq);


    $crud->closeConnection();
}

//testNotifications();




function rewindTransfer(): void {
    $crud = new AbstractCrudObject();
    $employeeHandler = $crud->employee();
    $employees = $employeeHandler->getByX();
    $paymentHandler = $crud->payments();
    $salesHandler = $crud->sale();


    $periodStrings = array("20211009-20211016","20211016-20211023","20211023-20211030","20211030-20211106");
    $finalEmployeeList = array();

    foreach ($periodStrings as $i => $periodString) {
        $period = $paymentHandler->periodIdToPeriod($periodString);

        $report = json_decode(file_get_contents(ROOT . "objects/payments/$periodString/report/employees.json"), true);
        $ids = array_map(function ($employee) { return (int)$employee["id"]; }, $report);

        $employeesOfPeriod = array_filter($employees, function ($employee) use ($ids, $period) {
            return !in_array((int)$employee["id"], $ids) && ($period["end"] > (int)$employee["created_at"]);
        });

        if($i !== 1) $prevPeriodReport = json_decode(file_get_contents(ROOT . "testLogs/".$periodStrings[ ($i -1) ].".json"), true);


        foreach ($employeesOfPeriod as $employee) {
            $employeeEarnings = $employeeHandler->getEarnings($employee["id"],0,0,$periodString);
            $salesOfPeriod = $salesHandler->salesOfPeriod(array("employee_id" => $employee["id"]), $periodString);

            $salesAmount = (float)$salesOfPeriod["cost"];
            $mentionEarnings = $employeeEarnings["earnings"];
            $earnings = ($salesAmount + $mentionEarnings);

            if(isset($prevPeriodReport) && !empty($prevPeriodReport)) {

                $prevPeriodEmployeeData = array_filter($prevPeriodReport, function ($item) use ($employee) { return $item["id"] === $employee["id"]; });
                if(!empty($prevPeriodEmployeeData)) {
                    $prevPeriodEmployeeData = array_values($prevPeriodEmployeeData)[0];
                    $earnings += $prevPeriodEmployeeData["transfer"];
                }


            }

            if($earnings < 5 && $earnings > 0) {
                $employee["transfer"] = $earnings;
                $finalEmployeeList[] = $employee;
            }
        }

        file_put_contents(ROOT . "testLogs/$periodString.json", json_encode($finalEmployeeList,JSON_PRETTY_PRINT));
        echo count($finalEmployeeList) . "<br>";
    }



    $crud->closeConnection();
}
//rewindTransfer();


#[ArrayShape(["upcoming" => "int[]", "payment_period_transfer" => "float|int", "pending_payment" => "float|int", "approved_payment" => "float|int", "total_paid" => "float|int"])]
function earningsTimeInterval(int $timeFrom = 0, int $timeTo = 0): array {
    $crud = new AbstractCrudObject();

    $paymentHandler = $crud->payments();
    $salesHandler = $crud->sale();
    $employeeHandler = $crud->employee();
    $salesOfPeriod = $salesHandler->salesOfPeriod();

    $data = array();
    $employees = $employeeHandler->getByX();
    $employeeHandler->disableDepthCheck();
    $costs = array("upcoming" => array("cost" => 0, "count" => 0), "payment_period_transfer" => 0, "pending_payment" => 0, "approved_payment" => 0, "total_paid" => 0);

    if(!empty($employees)) {
        foreach ($employees as $employee) {
            $costs["approved_payment"] += (float)$employee["approved_payment"];
            $costs["pending_payment"] += (float)$employee["pending_payment"];
            $costs["payment_period_transfer"] += (float)$employee["payment_period_transfer"];
            $costs["total_paid"] += (float)$employee["total_paid"];

            $data[] = $employeeHandler->getEarnings($employee["id"], $timeFrom, $timeTo);
        }
    }



    if(!empty($data)) {
        foreach ($data as $employeeEarnings) {
            $costs["upcoming"]["cost"] += (float)$employeeEarnings["earnings"];
            $costs["upcoming"]["count"] += (int)$employeeEarnings["entry_count"];
        }
        $costs["upcoming"]["cost"] += $salesOfPeriod["cost"];
    }

    if($paymentHandler->isNewPaymentPeriod()) $costs["pending_payment"] += $costs["payment_period_transfer"];
    else $costs["upcoming"]["cost"] += $costs["payment_period_transfer"];

    $crud->closeConnection();
    return $costs;
}

//echo json_encode(earningsTimeInterval(1637622000,1637658035));







function lookupEarnings(int $employeeId, bool $activePeriod = true){

    $crud = new AbstractCrudObject();
    $employeeHandler = $crud->employee();
    $paymentHandler = $crud->payments();

    $earningsStartTime = strtotime("2022-01-01");
    $earningsEndTime = strtotime("2022-06-04");
    $salesDirectEndTime = strtotime("2022-01-29");

    $employee = $employeeHandler->get($employeeId);
    $creationDate = date("d/m-Y", (int)$employee["created_at"]);

    $period = $activePeriod ? $paymentHandler->activePaymentPeriod() : $paymentHandler->lastPaymentPeriod();
    $periodString = $paymentHandler->createPeriodId($period);

    $igUserList = $employeeHandler->igUserList($employeeId, $employee["ig_users"]);
    $contentFile = $employeeHandler->getBaseFileContent($employeeId);

    /**
     * Uncomment below if you're looking to fetch their previous instagram claimed users' data too
     */
    if(array_key_exists("change_log", $contentFile)) {
        foreach ($contentFile["change_log"] as $item) {
            if($item["type"] === "claimed_users" && !in_array($item["id"], $igUserList)) $igUserList[] = $item["id"];
        }
    }

    $lifetimeSalesCount = count($crud->sale()->getByX(array("employee_id" => $employeeId)));


//    $employeeEarnings = $employeeHandler->getEarnings($employeeId, 0, 0, $periodString, $igUserList);
    $employeeEarnings = $employeeHandler->getEarnings($employeeId,$earningsStartTime, $earningsEndTime, "", $igUserList);

    wrapTextOutput(array(
        "entry_cost" => $employeeEarnings["earnings"],
        "entry_count" => $employeeEarnings["entry_count"],
        "current_transfer" => $employeeHandler->paymentPeriodTransfer($employeeId),
        "lifetime_sales_count" => $lifetimeSalesCount,
        "creation_date" => $creationDate
    ));


    $crud->closeConnection();
}

//lookupEarnings(4716, false);


function paymentTransferred(): void {
    $crud = new AbstractCrudObject();
    $employees = $crud->retrieve("user", array(),array(),"SELECT * FROM users WHERE payment_period_transfer != 0");
    $amount = array_reduce($employees, function ($initial, $now) {
        return (isset($initial) ? $initial : 0) + (float)$now["payment_period_transfer"];
    });
    echo round($amount,2);
    $crud->closeConnection();
}

function rollBackPaymentPeriodWithNoEarnings(){
    $crud = new AbstractCrudObject();
    $employeeHandler = $crud->employee();
    $paymentHandler = $crud->payments();
    $app = $crud->app();

    $employees = $employeeHandler->getByX();
    if(empty($employees)) return;

//    $crud->update("user",array(), array(),array(), "UPDATE users SET pending_payment = 0");

    $lastPeriod = $paymentHandler->lastPaymentPeriod();
    $update = $app->update(array(
        "payment_period_start" => $lastPeriod["start"],
        "payment_period_end" => $lastPeriod["end"],
        "last_payment_period_start" => 0,
        "last_payment_period_end" => 0
    ));

//    if($update) {
//        $lastPeriod = $paymentHandler->lastPaymentPeriod();
//        $app->update(array(
//            "last_payment_period_start" => $lastPeriod["start"],
//            "last_payment_period_end" => $lastPeriod["end"]
//        ));
//    }

}
//rollBackPaymentPeriodWithNoEarnings();



function rollBackApprovedPayment(): void{
    if(isset($_SESSION["FUCK_OFF"])) return;
    $_SESSION["FUCK_OFF"] = true;

    $crud = new AbstractCrudObject();
    $employeeHandler = $crud->employee();
    $reportFiles = array(
        "objects/payments/20220430-20220507/report/employees.json",
        "objects/payments/20220430-20220507/report/job_posters.json",
    );

    $total = 0;
    foreach ($reportFiles as $filename) {
        if(!file_exists(ROOT . $filename)) {
            echo $filename . "<br>";
            continue;
        }

        $report = json_decode(file_get_contents(ROOT . $filename), true);
        if(empty($report)) continue;

        foreach ($report as $item) {
            $totalCost = $item["total_cost"];
            $transfer = round($item["transferred_cost"] + $item["pending_difference_to_transfer"], 5);
            $pending = round($totalCost - $transfer, 5);

            $total+=$totalCost;
//            echo json_encode($pending) . "<br>";

            $params = array(
                "approved_payment" => 0,
                "pending_payment" => $pending,
                "payment_period_transfer" => $transfer,
            );
            $employeeHandler->update($params,array("id" => $item["id"]));
        }
    }

//    echo $total;



//    $employees = $employeeHandler->getUsersWithApprovedPayment();
//    echo count($employees);
//    if(empty($employees)) return;
//
//    $reportFiles = array(
//        "objects/payments/20220319-20220326/report/employees.json",
//        "objects/payments/20220319-20220326/report/job_posters.json",
//    );
//
//    $content = array();
//
//    foreach ($reportFiles as $filename) {
//        if(!file_exists(ROOT . $filename)) {
//            echo $filename . "<br>";
//            continue;
//        }
//
//        $report = json_decode(file_get_contents(ROOT . $filename), true);
//        if(empty($report)) continue;
//
//        $content = array_merge($content, $report);
//    }
//
//    foreach ($employees as $employee) {
//        $reportItem = array_values(array_filter($content, function ($item) use ($employee) {
//            return (int)$item["id"] === (int)$employee["id"];
//        }));
//
//        $totalCost = (float)$employee["approved_payment"];
//
//
//        if(empty($reportItem)) $transfer = 0;
//        else {
//            $reportItem = $reportItem[0];
//            $transfer = (float)$reportItem["pending_difference_to_transfer"];
//        }
//        $pending = round($totalCost - $transfer, 5);
//
//        $params = array(
//            "approved_payment" => 0,
//            "pending_payment" => $pending,
//            "payment_period_transfer" => $transfer,
//        );
//        $employeeHandler->update($params,array("id" => $employee["id"]));
//    }



    $crud->closeConnection();
}

//rollBackApprovedPayment();
//unset($_SESSION["FUCK_OFF"]);



function lookUpViolation(string|int $id = 0): void {

    $crud = new AbstractCrudObject();
    $violationHandler = $crud->violation();

    $violation = $violationHandler->get($id);
    if(empty($violation)) {
        echo "Violation doesn't exist";
        return;
    }


    $entryPath = $violation["error_file_path"];
    if(!file_exists(ROOT . $entryPath)) {
        echo "Could not find entry";
        return;
    }

    $entry = json_decode($crud->encrypt(file_get_contents(ROOT . $entryPath),true),true);
    if(empty($entry) || $entry === false || $entry === null ||!is_array($entry)) {
        echo "Could not get entry";
        return;
    }

    echo "found record! <br>";
    echo json_encode($entry);
    file_put_contents(ROOT . "testLogs/entryLookup.json", json_encode($entry,JSON_PRETTY_PRINT));

    $crud->closeConnection();
}

//lookUpViolation(5642);




function violationsResolveButNotDouble() {

    $crud = new AbstractCrudObject();
    $violationHandler = $crud->violation();
//    $violations = $violationHandler->getByX();
//    $recordsDirectory = $crud->storageManager()->objStoragePaths("records");

    $file = ROOT . "testLogs/violationsResolveSuccess_1634931985.json";
    $violations = json_decode(file_get_contents($file), true);

    $violations = array_map(function ($violation) { return $violation["violation"]["id"]; }, $violations);

    echo count($violations) . "<br>";
    echo json_encode($violations);

    echo "<br>";

    $success = 0;

    foreach ($violations as $id) {
        $status = $violationHandler->delete($id);

        if(is_array($status) && array_key_exists("success", $status) && $status["success"]); $success += 1;
    }

    echo $success;

    $crud->closeConnection();
    return true;






//
//    foreach ($violations as $violation) {
//        $entryPath = $violation["error_file_path"];
//        if(!file_exists(ROOT . $entryPath)) {
//            $error[] = array("reason" => "File not found", "violation" => $violation);
//            continue;
//        }
//
//        $entry = json_decode($crud->encrypt(file_get_contents(ROOT . $entryPath), true),true);
//        if(empty($entry) || !is_array($entry)) {
//            $error[] = array("reason" => "is not array or empty", "violation" => $violation, "entry" => $entry);
//            continue;
//        }
//        $entry["file_path"] = $entryPath;
//
//        $resolved = $violationHandler->entryNoViolations($entry,$recordsDirectory,true);
//
//        if(!$resolved){
//            $error[] = array("reason" => "Still a violation", "violation" => $violation, "entry" => $entry);
//            continue;
//        }
//
//
//        $success[] = array("violation" => $violation, "entry" => $entry);
//    }
//
//    if(!empty($error)) file_put_contents($storePathError, json_encode($error,JSON_PRETTY_PRINT));
//    if(!empty($success)) file_put_contents($storePathSuccess, json_encode($success,JSON_PRETTY_PRINT));
//


//    $crud->closeConnection();
}



function violationResolveScripts():void {
    ini_set('max_execution_time', '-1');
    set_time_limit(-1);
    $crud = new AbstractCrudObject();

    $violationHandler = $crud->violation();
    $employeeHandler = $crud->employee();
    $igHandler = $crud->igUser();

    $violations = $violationHandler->getByX(array("violation_type" => "wrong_script"));
    if(empty($violations)) return;

    echo count($violations) . "<br><br>";

    $collectUsernames = $idsToRemove = array();

    foreach ($violations as $violation) {
        if(array_key_exists($violation["username"],$collectUsernames)) $employeeId = $collectUsernames[ ($violation["username"]) ];
        else {
            $employeeId = $igHandler->employeeId($violation["username"]);
            $collectUsernames[ ($violation["username"]) ] = $employeeId;
        }

        $scriptList = $employeeHandler->employeeAvailableScripts($employeeId);
        if(empty($scriptList)) continue;

        if(!file_exists(ROOT . $violation["error_file_path"])) continue;
        $content = json_decode($crud->encrypt(file_get_contents(ROOT . $violation["error_file_path"]), true), true);

        if(empty($content)) continue;
        $text = preg_replace('!\s\s!', ' ', $content["text"],1);

        $mergedScripts = array_unique(array_map(function ($script) { return strtolower($script["merged_script"]); }, $scriptList));

        if(in_array(strtolower($text),$mergedScripts)) $idsToRemove[] = (int)$violation["id"];
    }

    if(!empty($idsToRemove)) {
        $sqlDelete = "DELETE FROM violations WHERE id IN (".implode(",",$idsToRemove).")";
        $sqlGet = "SELECT * FROM violations WHERE id IN (".implode(",",$idsToRemove).")";

        echo count($idsToRemove) . "<br><br>";
        echo json_encode($idsToRemove) . "<br><br>";

        $getMe = $crud->retrieve("violation", array(), array(),$sqlGet);
        echo count($getMe) . "<br><br>";
        echo json_encode($getMe) . "<br><br>";
//        $crud->delete("violation", array(),$sqlDelete);
    }



    $crud->closeConnection();
    ini_set('max_execution_time', '150');
    set_time_limit(150);
}

//violationResolveScripts();







function getRawSaves(): array {

    $crud = new AbstractCrudObject();
    $rawSavePath = STORAGE . STORAGE_RAW_SAVES;
    $rawSaves = $crud->grabDirContent($rawSavePath . "*.txt", false, true);
    if(empty($rawSaves)) return array();

    $content = array();
    sleep(5); //By chance, we grab an ongoing webhook entry. Let's make sure it's been handled

    foreach ($rawSaves as $rawSave) {
        if(!file_exists($rawSave)) continue;
        $get = json_decode($crud->encrypt(file_get_contents($rawSave), true),true);

        if(!empty($get)) $content[] = $get;
    }

    $crud->closeConnection();
    return $content;
}

//$content = getRawSaves();

function pullUpIgUserRecords(string $username): array {
    $crud = new AbstractCrudObject();
    $igUserHandler = $crud->igUser($username);

    $item = $igUserHandler->get();
    echo date("Y-m-d H:i:s", ((int)$item["created_at"]));

    $records = $igUserHandler->records();
    $crud->closeConnection();

    return $records;
}

function correctBadUsers(): void {
    $collect = array();
    $crud = new AbstractCrudObject();

    $igUserHandler = $crud->igUser();
    $employeeHandler = $crud->employee(1);

    $path = ROOT . "testLogs/corruptIgs.json";
    $corrupted = json_decode(file_get_contents($path));
    $employees = $employeeHandler->getByX();

    foreach ($employees as $employee) {
        $list = $employeeHandler->igUserList($employee["id"]);

        foreach ($corrupted as $item) {
            if(in_array($item,$list)) $collect[] = array("employee_id" => $employee["id"], "ig_user" => $item);
        }
    }

    if(count($collect) !== count($corrupted)) {
        echo "counts are OFF!";
        return;
    }

    $success = 0;
    foreach ($collect as $item)
        if($igUserHandler->update(array("employee_id" => $item["employee_id"]), array("username" => $item["ig_user"]))) $success += 1;


    echo "successes: " . $success;


    $crud->closeConnection();
}



//$crud = new AbstractCrudObject();
//$storageManager = $crud->storageManager();
//$content = pullUpIgUserRecords("balanzifashionangelsxx");
//
//if(!empty($content)) {
//    $keys = array_keys($content[0]);
//    $keys[] = "post_id";
//    $crud->sortByKey($content, "timestamp");
//}

//echo json_encode($crud->igUser("balanziluxuryjewlery")->get());


$reportHandler = (new AbstractCrudObject())->reporting();
$dataSets = (new AbstractCrudObject())->dataSet();
$crud = new AbstractCrudObject();
//$regularParams = array(
//    "employee" => array("period"),
//);
//$sharedData = $crud->reporting()->sharedData(
//    $regularParams, false, 22,array(), false, strtotime("last friday")
//);

//echo memory_get_usage() . " of " . memory_get_usage(true) . "<br>";
//
//echo memory_get_peak_usage() . " of " . memory_get_peak_usage(true) . "<br>";


//$sharedData = $crud->reporting()->violationAnalytics(array(
//    "fields" => array("time_start" => strtotime("today"), "time_end" => strtotime("tomorrow") -1)
//));



//(new AbstractCrudObject())->adminDebugWriteFile($sharedData, "sometimetongting.json");



$pageTitle = "Admin's paradise";
?>
<script>
    var pageTitle = <?=json_encode($pageTitle)?>;
    var sharedData = <?=json_encode(isset($sharedData) ? $sharedData : array())?>;
</script>
<div class='page-content'>
    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-body wrap">
                    <p class="font-20">

                        <?php
//                            $crud = new AbstractCrudObject();
                        ?>


                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($keys)): ?>
    <div class="row">
        <div class="col-sm-12 mt-4">
            <div class="card border-radius-15px ">
                <div class="card-body">
                    <p class="font-20">Raw saves</p>
                    <div class="table-responsive mt-1 height-card-H">
                        <table class="table table-hover dataTable prettyTable" id="TableDataContent">
                            <thead>
                                <tr>
                            <?php foreach ($keys as $key): ?>
                                <th><?=Titles::cleanUcAll($key)?></th>
                            <?php endforeach; ?>
<!--                            <th>Response</th>-->
<!--                            <th>Time</th>-->
<!--                            <th>Username</th>-->
<!--                            <th>Post link</th>-->
<!--                            <th>Mentioned account</th>-->
<!--                            <th>Comment</th>-->
                                </tr>
                            </thead>
                            <tbody>
                            <?php

                            if(!empty($content)):
//                                $crud->sortByKey($content,"timestamp");
                                foreach ($content as $item): ?>
                                    <tr>
                                    <?php foreach ($item as $field => $column):
                                        $value = $field === "timestamp" ? date("d-m-Y H:i:s",$column) : $column;
                                        $value = $field === "permalink" ? "<a href='$value' target='_blank'>$value</a>" : $value;
                                        $value = is_array($value) ? json_encode($value) : $value;
                                    ?>
                                        <td><?=$value?></td>
<!--                                        <td>--><?//=is_array($item) ? json_encode($item) : $item?><!--</td>-->
<!--                                        <td>--><?//=date("F d, H:i",$item["timestamp"])?><!--</td>-->
<!--                                        <td>--><?//=$item["username"]?><!--</td>-->
<!--                                        <td><a href="--><?//=$item["permalink"]?><!--" target="_blank">Open</a></td>-->
<!--                                        <td>--><?//=$item["mentioned_instagram_username"]?><!--</td>-->
<!--                                        <td>--><?//=$item["text"]?><!--</td>-->
                                <?php endforeach; ?>
                                        <td><?=$storageManager->instagramPostId($item["permalink"]);?></td>
                                    </tr>
                                <?php endforeach;
                            endif;
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>



    <div class='mt-5 p-2 previewPostContainer border-radius-10px color-white' style='width: 400px;'>
        
        
        <div class='flex-row-start flex-align-center w-100'>
            <div class='flex-row-start flex-align-center' style='width: 50px; !important;' >
                <a href='https://www.instagram.com/p/B1j3EwQAXgb/' target='_blank'>
                    <img src='<?=HOST?>images/nopp.png' class='border-radius-50 w-100' />
                </a>
            </div>

            <div class='flex-col-start'>
                <div class='flex-row-start flex-align-center ml-3 border-bottom border-light' >
                    <p class='font-16'>Name:</p>
                    <p class='font-16 ml-2'>Frederik amdlaj </p>
                </div>

                <div class='flex-row-between flex-align-center ml-3' >
                    <div class='flex-row-start flex-align-center font-16'>
                        <p class='font-16'>Posts:</p>
                        <p class='font-16 ml-2'>35</p>
                    </div>
                    <div class='flex-row-start flex-align-center font-16 ml-3'>
                        <p class='font-16 font-weight-light'>Followers:</p>
                        <p class='font-16 ml-2'>48K</p>
                    </div>
                </div>
            </div>
        </div>

        <div class='flex-row-between mt-3 w-100'>
            <div class='flex-col-start flex-wrap mr-2' style='width: 300px; !important;'>
                <div class='flex-row-between flex-align-center border-bottom border-light'>
                    <p class='font-16'>Post description</p>
                    <a href='https://www.instagram.com/p/B1j3EwQAXgb/' target='_blank' class='color-red hover-underline'>
                        <i class='mdi mdi-open-in-new'></i> View post
                    </a>
                </div>
                <p class='font-14 mt-1'>Frederik amd dhewdh ewjdlsa ddsfhhdsbfk dn ajdaldj  fhsdhfasnldjasoid dsfh idsh fsjp fg spf laj </p>
            </div>
            <div class='flex-row-end flex-align-center' style='width: 80px; !important;' >
                <img src='<?=HOST?>images/nopp.png' class='border-radius-20px w-100' />
            </div>
        </div>
        
        
        
        
        
    </div>

    <div class='mt-5'>

        <div class='p-2 previewPostContainer border-radius-10px color-white position-relative' style='width: 400px;'>
            <div class='flex-row-start flex-align-center w-100' >
                <div class='flex-row-start flex-align-center' style='width: 50px; !important;' >
                    <a href='https://www.instagram.com/p/B1j3EwQAXgb/' target='_blank'>
                        <img src='<?=HOST?>images/nopp.png' class='border-radius-50 w-100' />
                    </a>
                </div>

                <div class='flex-col-start'>
                    <div class='flex-row-start flex-align-center ml-3 border-bottom border-light' >
                        <span class='border-radius-20px mb-1' style='height: 20px; width:190px; background: rgba(238,238,238,.2)'></span>
                    </div>

                    <div class='flex-row-between flex-align-center ml-3' >
                        <span class='border-radius-20px mb-1' style='height: 20px; width:190px; background: rgba(238,238,238,.2)'></span>
                    </div>
                </div>
            </div>

            <div class='flex-row-between mt-3 w-100'>
                <div class='flex-col-start flex-wrap mr-2' style='width: 300px; !important;'>
                    <div class='flex-row-between flex-align-center border-bottom border-light'>
                        <span class='border-radius-20px mb-1' style='height: 20px; width:115px; background: rgba(238,238,238,.2)'></span>
                        <span class='border-radius-20px mb-1' style='height: 20px; width:80px; background: rgba(238,238,238,.2)'></span>
                    </div>
                    <span class='border-radius-20px mb-1' style='height: 20px; width:295px; background: rgba(238,238,238,.2)'></span>
                    <span class='border-radius-20px mb-1' style='height: 20px; width:295px; background: rgba(238,238,238,.2)'></span>
                </div>
                <div class='flex-row-end flex-align-center' style='width: 80px; !important;' >
                    <img src='<?=HOST?>images/nopp.png' class='border-radius-20px w-100' />
                </div>
            </div>


            <div class='abs-center-2rem spinner-grow color-white' role='status'>
                <span class='sr-only'></span>
            </div>
        </div>
    </div>



    <div class='mt-5'>

        <div class='p-2 previewPostContainer border-radius-10px color-white position-relative' style='width: 400px;'>
            <div class='flex-row-around flex-align-center m-2'>
                <p class='font-16'>Failed to fetch post-preview</p>
            </div>
        </div>
    </div>





</div>
<?php
if(isset($crud)) $crud->closeConnection();