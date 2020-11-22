<?php
namespace de\interaapps\ulole\core\cli\modules;

use de\interaapps\ulole\core\cli\CLI;
use de\interaapps\ulole\core\cli\CLIHandler;
use de\interaapps\ulole\core\cli\Colors;
use de\interaapps\ulole\orm\UloleORM;

class DBNavigatorCLI extends CLIHandler {
    
    public function registerCommands(CLI $cli) {
        /**
         * ToDO
         * > insert users
         * syntax "name":"Test","mail":"test@"
         * >  "name":"Test","mail":"test@"
         * inserted.
         *  (Uses json)
         */
        $cli->register("db:nav", function(){
            $allTables = [];
            $db = UloleORM::getDatabase('main');
            $currentScreen = [
                "action" => "TABLES"
            ];
    
            while (true) {
                $action = $currentScreen["action"];
                if ($action == 'TABLES' || $action == 'SHOW_TABLES') {
                    $tables = $db->getConnection()->query("SHOW TABLES;")->fetchAll(\PDO::FETCH_NUM);
                    $allTables = [];
                    foreach ($tables as $index=>$table){
                        array_push($allTables, $table[0]);
                        echo Colors::YELLOW.($index+1)." ".Colors::TURQUIOUS." ".$table[0].Colors::ENDC."\n";
                    }
                    $currentScreen["options"] = $tables;
                } if ($action == 'QUERY' || $action == 'TABLE') {
                    $tableAction = $action == 'TABLE';
                    echo Colors::GRAY. $currentScreen["query"].Colors::ENDC."\n";
                    $res = $this->responseTransformer($db, $currentScreen["query"], $tableAction);
                    
                    

                    if ($res["type"] == 'ENTRIES') {
                        $columns = $res["columns"];
                        $entries = $res["entries"];

                        $columnPads = [];

                        foreach ($entries as $entry){
                            foreach ($entry as $columnName=>$value) {
                                if (!isset($columnPads[$columnName]) || (isset($columnPads[$columnName]) && strlen($value) > $columnPads[$columnName]))
                                    $columnPads[$columnName] = strlen($value)+2;

                                if ($columnPads[$columnName] > 30)
                                    $columnPads[$columnName] = 30;
                            }
                        }
                        $splitter = Colors::GRAY." | ".Colors::ENDC;
                        $opened = true;
                        foreach ($columns as $column) {
                            echo
                                ($opened ? "" : $splitter) 
                                .Colors::YELLOW
                                .str_pad($column, $columnPads[$column])
                                .Colors::ENDC;
                            $opened = false;
                        }
                        echo "\n";

                        foreach ($entries as $entry){
                            $opened = true;
                            foreach ($entry as $columnName=>$value) {
                                $length = $columnPads[$columnName];

                                echo ($opened ? "" : $splitter)
                                        .substr(str_pad($value, $length), 0, $length);
                                $opened = false;
                            }
                            echo "\n";
                        }
                        if ($tableAction)
                        echo "\n".
                            Colors::BG_BLACK." ".
                                (
                                    Colors::GREEN.
                                    "(+) NEXT PAGE"
                                ).
                                " ".Colors::TURQUIOUS."| ".
                                (
                                    Colors::GREEN.
                                    "(-) NEXT PAGE"
                                ).
                            " ".Colors::ENDC."\n";
                    } else if ($res["type"] == 'QUERY') {
                        if ($res["type"]) {
                            Colors::done(Colors::GRAY."no entries returned".Colors::ENDC);
                        } else {
                            Colors::error("SQL Error: ".$res["statement"]->errorInfo());
                        }
                    }
                }


                readline_completion_function(function() use ($action, $allTables) {
                    $matches = [];
                    $matches = array_merge($matches, $allTables);
                    if ($action == 'SQL') {
                        $keywords = [
                            "INSERT", "FROM",
                            "SELECT", "SET",
                            "UPDATE", "WHERE",
                            "DELETE", "CREATE", 
                            "TABLE", "IF", "NOT", 
                            "SHOW", "TABLES", "INTO", "VALUES", "()",
                            "ALTER", "CHANGE", "MODIFY",
                            "AND", "BEFORE", "BY", "CALL",
                            "CASE", "CONDITION", "DESC", "DESCRIBE",
                            "GROUP", "IN", "INDEX", "INTERVAL",
                            "IS", "KEY", "LIKE", "LIMIT", "LONG", "MATCH", 
                            "NOT", "OPTION", "OR", "ORDER", 
                            "PARTITION", "REFERENCES", "TO", 
                            "CHAR", "VARCHAR", "TINYTEXT", "TEXT",
                            "BLOB", "MEDIUMTEXT", "MEDIUMBLOB", "LONGTEXT", "LONGBLOB",
                            "ENUM", "SET", "TINYINT", "SMALLINT", "MEDIUMINT",
                            "INT", "BIGINT", "FLOAT", "DOUBLE", "DECIMAL",
                            "DATE", "DATETIME", "TIMESTAMP", "TIME", "YEAR"
                        ];
                        foreach ($keywords as $keyword)
                            array_push($matches, strtolower($keyword));
                        
                        foreach ($keywords as $keyword)
                            array_push($matches, $keyword);
                    } else {
                        $matches = array_merge($matches, [
                            "tables",
                            "help",
                            "sql"
                        ]);
                    }
                    return $matches;
                });
                
                $input = "";
                if ($action == 'SQL') {
                    $currentScreen["query"] .= "\n" . readline("- ");
                } else
                    $input = readline("> ");


                if (substr(strtolower($input), 0, 3) == 'sql') {
                    $currentScreen = [
                        "action" => "SQL",
                        "query"  => substr($input, 4, strlen($input))
                    ];
                    $action = "SQL";
                } else {
                    readline_add_history($input);
                    //echo $input;
                    if (strtolower($input) == 'tables') {
                        $currentScreen = [
                            "action" => "SHOW_TABLES"
                        ];
                        $action = "SHOW_TABLES";
                    }
                }

                if ($action == "TABLES" && array_key_exists($input-1, $currentScreen['options'])) {
                    $currentScreen = [
                        "action" => "TABLE",
                        "name"   => $currentScreen['options'][$input-1][0],
                        "limit"  => 5,
                        "page"   => 0,
                        "query"  => "SELECT * FROM `".($currentScreen['options'][$input-1][0])."` LIMIT 5;"
                    ];
                } else if ($action == "TABLE") {
                    if (isset($currentScreen['name'])) {
                        
                        if (strpos($input, "=") !== false) {
                            [$option, $optionValue] = explode("=",$input, 2);
                            $option = trim($option);
                            $optionValue = trim($optionValue);
                            
                            if (strtolower($option) == 'limit') {
                                if (is_numeric($optionValue))
                                    $currentScreen['limit'] = $optionValue;
                                else Colors::error("Has to be a number!");
                            } else if (strtolower($option) == 'id') {
                                if (is_numeric($optionValue))
                                    $currentScreen['id'] = $optionValue;
                                else unset($currentScreen['id']);
                            }
                        } else if ($input == '+') {
                            echo "Upper";
                            if (isset($currentScreen['page'])) $currentScreen['page'] += 1;
                        } else if ($input == '-') {
                            if (isset($currentScreen['page']) && $currentScreen['page'] != 0) $currentScreen['page'] -= 1;
                        }
                        echo $currentScreen['limit']."\n";
                        echo $currentScreen['page']."\n";
                        $currentScreen = array_merge($currentScreen, [
                            "action" => "TABLE",
                            "name"   => $currentScreen['name'],
                            "query"  => "SELECT * FROM `".$currentScreen['name']."` "
                                ." ".(isset($currentScreen['id']) ? "WHERE id=".$currentScreen['id'] : "")      
                                ." LIMIT ".(isset($currentScreen['limit']) ? $currentScreen['limit'] : "5") 
                                ." OFFSET ".(isset($currentScreen['page']) && isset($currentScreen['limit']) ? $currentScreen['limit']*$currentScreen['page'] : "0")
                                
                                .";"
                        ]);
                    }
                } else if ($action == "QUERY") {
                    $currentScreen = [
                        "action" => "NONE"
                    ];
                } else if($action == 'SQL') {
                    if ( substr(trim($currentScreen['query']), -1) == ';') {
                        $currentScreen = [
                            "action" => "QUERY",
                            "query"  => $currentScreen["query"]
                        ];
                        readline_add_history($currentScreen["query"]);
                    } else if (substr(trim($currentScreen['query']), -1) == '\\') {
                        $currentScreen['query'] = rtrim($currentScreen['query'], "\\");
                    }
                }
            }
        }, "A simplified database-navigator");
    }

    private function responseTransformer($db, $sql, $tableAction){
        $query = $db->getConnection()->query($sql);
        $response = ["type" => "NONE", "statement" => $query];

        if ($query === false) {
            $response = ["type" => "request", "success" => false];
        } else if ($query->rowCount() === 0 && !$tableAction) {
            $response = ["type" => "request", "success" => true];
        } else {
            $entries = $query->fetchAll(\PDO::FETCH_OBJ);
            $columnsChecked = false;
            $response["type"]    = "ENTRIES";
            $response["columns"] = [];
            $response["entries"] = [];

            foreach ($entries as $entry){
                $columnEntry = [];
                foreach ($entry as $column=>$value) {
                    if (!$columnsChecked)
                        array_push($response["columns"], $column);
                    $columnEntry[$column] = $value;
                }
                array_push($response["entries"], $columnEntry);
                
                $columnsChecked = true;
            }
        }
        return $response;
    }
    
}