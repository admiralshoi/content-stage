<?php
namespace classes\src\Object;
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();

use classes\src\AbstractCrudObject;

class CronWorker {
    private AbstractCrudObject $crud;
    private string $type = "";
    public int $timeStamp = 0;
    private array $info = [];
    protected bool $log = true;
    private array $typesList = array(
        "hashtag_tracking" => array(
            "log_file" => CRON_LOGS."cronLog_hashtag_tracking.log",
            "log_date_file" => CRON_LOGS."cronDate_hashtag_tracking.log",
            "log_memory_file" => CRON_LOGS."cronMemory_hashtag_tracking.log",
            "row_id" => 1,
            "time_gab" => (1800 * 2),
            "max_run_time" => (60 * 5),
            "sleep_timer" => (60 * 0)
        ),
        "media_update" => array(
            "log_file" => CRON_LOGS."cronLog_media_update.log",
            "log_date_file" => CRON_LOGS."cronDate_media_update.log",
            "log_memory_file" => CRON_LOGS."cronMemory_media_update.log",
            "row_id" => 2,
            "time_gab" => (60 * 5 - 10),
            "max_run_time" => (60 * 3),
            "sleep_timer" => (60 * 0)
        ),
        "tag_mentions" => array(
            "log_file" => CRON_LOGS."cronLog_tag_mentions.log",
            "log_date_file" => CRON_LOGS."cronDate_tag_mentions.log",
            "log_memory_file" => CRON_LOGS."cronMemory_tag_mentions.log",
            "row_id" => 3,
            "time_gab" => (60 * 5 - 10),
            "max_run_time" => (60 * 3),
            "sleep_timer" => (60 * 0)
        ),
        "account_insights" => array(
            "log_file" => CRON_LOGS."cronLog_account_insights.log",
            "log_date_file" => CRON_LOGS."cronDate_account_insights.log",
            "log_memory_file" => CRON_LOGS."cronMemory_account_insights.log",
            "row_id" => 4,
            "time_gab" => (60 * 20 - 10),
            "max_run_time" => (60 * 5),
            "sleep_timer" => (60 * 0)
        ),
    );
    function __construct(AbstractCrudObject $crud,string $type){
        $this->crud = $crud;
        $this->type = $type;
    }

    public function log($string,$init = false):void {
        $type = $this->typesList[$this->type];
        if($init) {
            if(file_exists($type["log_date_file"])) {
                $dates = file_get_contents($type["log_date_file"]);
                $dates = explode(PHP_EOL,$dates);
            }
            else $dates = [];
            if(count($dates) >= CRONLOG_MAX_ENTRIES && !empty($dates[(CRONLOG_MAX_ENTRIES-1)])) {
                file_put_contents($type["log_date_file"],"");
                file_put_contents($type["log_file"],"");
                file_put_contents($type["log_memory_file"],"");
            }
            file_put_contents($type["log_date_file"],time().PHP_EOL,FILE_APPEND);
            file_put_contents($type["log_file"],PHP_EOL."<b style='font-size: 20px;'>Log initiation at => ".date("d/m-Y H:i:s",time())."</b>".PHP_EOL,FILE_APPEND);
            $this->memoryLog("",true);
        } else {
            file_put_contents($type["log_file"],$string.PHP_EOL,FILE_APPEND);
        }
    }
    public function memoryLog(string $keyWord="", $init = false):void{
        $type = $this->typesList[$this->type];
        if($init) {
            file_put_contents($type["log_memory_file"],PHP_EOL,8);
            return;
        }

        $date = date("d/m-Y H:i:s",time());
        $string = $date." => ".memory_get_usage()." of total " . memory_get_usage(true) . " (".$keyWord.") ".PHP_EOL;
        file_put_contents($type["log_memory_file"],$string,8);
    }


    public function canRun(): bool {
        $tableType = "cronJob";
        $type = $this->typesList[$this->type];
        $params = array("can_run" => 1, "id" => $type["row_id"]);
        return ($this->crud->check($tableType,$params) === 1 && (($this->timeStamp + $type["max_run_time"]) > time()));
    }

    public function init(int $stamp, bool $forceStart = false): bool {
        if(!array_key_exists($this->type,$this->typesList)) return false;

        $this->timeStamp = $stamp;
        $access_level = 8;
        $tableType = "cronJob";
        $id = array("id" => $this->typesList[$this->type]["row_id"]);
        $requestInfo = $this->crud->retrieve($tableType,$id);
        $this->info = $requestInfo[0];
        $type = $this->typesList[$this->type];

        if((int)$this->info["access_level"] > $access_level) return false;

        if($forceStart) {
            $setValues = array("is_running" => 1, "started_at" => $stamp, "can_run" => 1);
            $this->crud->update($tableType,array_keys($setValues),$setValues,$id);

            $this->log("",true);
            return true;
        }



        if((int)$this->info["can_run"] === 1 && (int)$this->info["is_running"] === 0) {
            $slept = ($stamp-(int)$this->info["finished_at"]);
            if($type["sleep_timer"] > $slept) {//Ensures that at least x minutes pass in between pauses
                $this->log("Pause ends in ".($type["sleep_timer"] - $slept)." seconds");
//                sleep(($type["sleep_timer"] - $slept));
                return false;
            }

            $setValues = array("is_running" => 1, "started_at" => $stamp);
            $this->crud->update($tableType,array_keys($setValues),$setValues,$id);

            $this->log("<b><h5>Ending break and resuming... => ".date("d/m-Y H:i:s")."</h5></b>");
        } elseif(((int)$this->info["can_run"] === 0 && (int)$this->info["is_running"] === 0) ||
            (int)$this->info["can_run"] === 1 && (int)$this->info["is_running"] === 1) {
            $min_time_gab = $type["time_gab"]; //Min 24 hours
            $time_diff = $stamp - (int)$this->info["started_at"];

            if($min_time_gab > $time_diff) return false;

            $setValues = array("is_running" => 1, "started_at" => $stamp, "can_run" => 1);
            $this->crud->update($tableType,array_keys($setValues),$setValues,$id);
        }  else {
            $this->end();
            return false;
        }

        $this->log("",true);

        return true;
    }

    public function end(): void {
        $tableType = "cronJob";
        $id = array("id" => $this->typesList[$this->type]["row_id"]);
        $setValues = array("is_running" => 0, "finished_at" => time(), "can_run" => 0);
        $this->crud->update($tableType,array_keys($setValues),$setValues,$id);
    }

    public function pause(): bool {
        $tableType = "cronJob";
        $id = array("id" => $this->typesList[$this->type]["row_id"]);
        $requestInfo = $this->crud->retrieve($tableType,$id);
        $info = $requestInfo[0];
        if(((int)$info["can_run"] === 0 && (int)$info["is_running"] === 0)) {
            $this->log("<u>The cronjob was manually terminated.</u>");
            return false;
        }

        $setValues = array("is_running" => 0, "finished_at" => time(), "can_run" => 1);
        $this->crud->update($tableType,array_keys($setValues),$setValues,$id);
        $this->log("<u>Pausing cronJob => ".date("d/m-Y H:i:s")."</u>");
        return true;
    }


    public function finishedAt(): int { return array_key_exists("finished_at", $this->info) ? (int)$this->info["finished_at"] : 0; }


}