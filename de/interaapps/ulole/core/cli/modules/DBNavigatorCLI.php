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
        $cli->register("db", function(){
            $allTables = [];
            $db = UloleORM::getDatabase('main');
            $dbQueries = [
                "show_tables" => "SHOW TABLES;",
                "auto_increment" => "AUTO_INCREMENT"
            ];
            $driver = $db->getConnection()->getAttribute(\PDO::ATTR_DRIVER_NAME);
            // Database compatibility
            if ($driver == 'mysql') {

            } else if ($driver == "sqlite") {
                $dbQueries["show_tables"] = "SELECT name FROM sqlite_master WHERE type='table';";
                $dbQueries["auto_increment"] = "PRIMARY KEY AUTOINCREMENT";
            } else if ($driver == "postgres") {
                $dbQueries["show_tables"] = "SELECT tablename FROM pg_catalog.pg_tables;";
                $dbQueries["auto_increment"] = "SERIAL PRIMARY KEY"; // This doesn't work.
            } else if ($driver == "dblib") {
                $dbQueries["show_tables"] = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE='BASE TABLE';";
                $dbQueries["auto_increment"] = "IDENTITY(1,1) PRIMARY KEY";
            }
            $db->getConnection()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            $currentScreen = [
                "action" => "TABLES"
            ];
    
            while (true) {
                $action = $currentScreen["action"];
                if ($action == 'TABLES') {
                    $tables = $db->getConnection()->query($dbQueries["show_tables"])->fetchAll(\PDO::FETCH_NUM);
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
                    
                    if (isset($currentScreen['removeAfter']) && $currentScreen['removeAfter'])
                        $currentScreen = ["action" => "NONE"];

                    if ($res["type"] == 'ENTRIES') {
                        $columns = $res["columns"];
                        $entries = $res["entries"];

                        $columnPads = [];
                        $columnMaxLength = 30;
                        if (isset($currentScreen["len"]))
                            $columnMaxLength = $currentScreen["len"];

                        foreach ($columns as $column)
                            $columnPads[$column] = strlen($column)+( isset($currentScreen["order"]) ? 1 : 0 );

                        foreach ($entries as $entry){
                            foreach ($entry as $columnName=>$value) {
                                if (!isset($columnPads[$columnName]) || (isset($columnPads[$columnName]) && strlen($value) > $columnPads[$columnName]))
                                    $columnPads[$columnName] = strlen($value)+1;
                                
                                if (count($entries) !== 1 && $columnPads[$columnName] > $columnMaxLength)
                                    $columnPads[$columnName] = $columnMaxLength;
                            }
                        }
                        $splitter = Colors::GRAY." | ".Colors::ENDC;
                        $opened = true;
                        foreach ($columns as $column) {
                            $arrow = "";
                            if (isset($currentScreen["order"])) {
                                if (strtolower($currentScreen["order"]) == strtolower($column))
                                    $arrow = "▼";
                                else if (strtolower($currentScreen["order"]) == strtolower($column)." desc")
                                    $arrow = "▲";
                            }
                            echo
                                ($opened ? "" : $splitter) 
                                .Colors::YELLOW
                                .str_pad($column.$arrow, $columnPads[$column]).Colors::ENDC;
                            $opened = false;
                        }
                        echo "\n";

                        foreach ($entries as $entry){
                            $opened = true;
                            foreach ($entry as $columnName=>$value) {
                                $length = $columnPads[$columnName];

                                echo ($opened ? "" : $splitter)
                                        .substr(str_pad(str_replace("\n", "\\n", $value), $length), 0, $length);
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
                                    "order={name} {desc?}"
                                ).
                                " ".Colors::TURQUIOUS."| ".
                                (
                                    Colors::GREEN.
                                    "limit={limit:5}"
                                ).
                                " ".Colors::TURQUIOUS."| ".
                                (
                                    Colors::GREEN.
                                    "where={query}"
                                ).
                                " ".Colors::TURQUIOUS."| ".
                                (
                                    Colors::GREEN.
                                    "(-) PREV PAGE"
                                ).
                            " ".Colors::ENDC."\n";
                    } else if ($res["type"] == 'request') {
                        if ($res["success"]) {
                            Colors::done(Colors::GRAY."no entries returned".Colors::ENDC);
                        } else {
                            $error = $db->getConnection()->errorInfo();
                            Colors::error("SQL Error: #".$error[1].Colors::GRAY." (".$error[0].") ".Colors::ENDC.$error[2]);
                        }// create table if not exists `test` ()
                    }
                }


                readline_completion_function(function($text) use ($action, $allTables) {
                    $matches = [];
                    $matches = array_merge($matches, $allTables);
                    if ($action == 'SQL' || strtolower(substr(readline_info()['line_buffer'], 0, 3)) == 'sql') {
                        $keywords = [
                            "INSERT", "FROM",
                            "SELECT", "SET",
                            "UPDATE", "WHERE",
                            "DELETE", "CREATE", 
                            "TABLE", "IF", "NOT", "EXISTS",
                            "SHOW", "TABLES", "INTO", "VALUES", "JOIN",
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
                    
                    if (strtolower(trim($input)) == 'tables') {
                        $currentScreen = [
                            "action" => "TABLES"
                        ];
                        $action = "TABLES";
                        continue;
                    }
                }


                if (strtolower(trim($input)) == 'exit' || strtolower(trim($input)) == 'quit' || strtolower(trim($input)) == 'logout') {
                    exit();
                    break;
                } else if (strtolower(trim($input)) == 'help') {
                    $helpList = [
                        "help" => [
                            "help" => "Shows you a list with commands",
                            "help:sql" => "Shows a SQL-Cheatsheet"
                        ],
                        "table" => [
                            "+"     => "Next page",
                            "-"     => "Previous page",
                            "limit" => "Set the entries limit (limit={number:5})",
                            "where" => "Set where clause (where={query}; example: where = id='1' OR id='4')",
                            "order" => "Set the order by (order={field}; example: order = id; order = password desc (descending order)",
                        ],
                        "navigation" => [
                            "tables" => "Let's you show tables which you can select",
                            "sql"    => "Opens a multiline SQL-input. Enter with ';' at the end of the statement."
                        ],
                    ];

                    echo "\n".Colors::LIGHT_BLUE."Help list: ".Colors::ENDC."\n";
                    foreach ($helpList as $name=>$entries) {
                        echo Colors::BLUE.$name.Colors::YELLOW.":".Colors::ENDC."\n";
                        foreach ($entries as $entry => $description){
                            echo "  ".Colors::BOLD.Colors::TURQUIOUS.str_pad($entry.Colors::ENDC.Colors::BLUE.":", 18)." ".Colors::GRAY.$description.Colors::ENDC."\n";
                        }
                        echo "\n";
                    }
                    continue;
                } else  if (strtolower(trim($input)) == 'help:sql') {
                    $helpList = [
"Create Table" => 
"CREATE TABLE `name` ( 
    `id` INT ".$dbQueries["auto_increment"].", 
    `name` TEXT, 
    PRIMARY KEY (`id`) # If PRIMARY KEY is not set in `id`
);",
"Delete (Drop) Table" => "DROP TABLE `name`;",
"Add Column" => 
"ALTER TABLE ADD `name` `password` TEXT NOT NULL; # ADD <column_name> <type> <options...>",
"Edit Column" => 
"ALTER TABLE `name` CHANGE `password` `new_password` TEXT NULL; # CHANGE <column> <rename_name> <type> <options...>",

"Insert entry into Table" => 
"INSERT INTO `name` (`name`) VALUES ('test');",

                    ];

                    $replacements = [
                        "#\\" => Colors::GRAY."#\\".Colors::ENDC,
                        "CREATE" => Colors::BLUE."CREATE".Colors::ENDC,
                        "TABLE" => Colors::LIGHT_BLUE."TABLE".Colors::ENDC,
                        "INSERT" => Colors::LIGHT_BLUE."INSERT".Colors::ENDC,
                        "INTO" => Colors::BLUE."INTO".Colors::ENDC,
                        "(" => Colors::TURQUIOUS."(".Colors::ENDC,
                        ")" => Colors::TURQUIOUS.")".Colors::ENDC,
                    ];

                    echo "\n".Colors::LIGHT_BLUE."SQL-Cheatsheet list: ".Colors::ENDC."\n";
                    foreach ($helpList as $name=>$sql) {
                        foreach ($replacements as $from=>$to)
                            $sql = str_replace($from, $to, $sql);
                        echo "\n".Colors::BOLD.Colors::TURQUIOUS."# ".$name.Colors::ENDC.Colors::YELLOW.":".Colors::ENDC."\n".$sql."\n";
                    }
                    echo "\n";
                    continue;
                } else if ($action == "TABLES" && is_numeric(trim($input)) && array_key_exists($input-1, $currentScreen['options'])) {
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
                            
                            //           limit
                            if (strtolower($option) == 'limit') {
                                if (is_numeric($optionValue))
                                    $currentScreen['limit'] = $optionValue;
                                else Colors::error("Has to be a number!");
                            //           Max column length
                            } else if (strtolower($option) == 'len') {
                                if (is_numeric($optionValue))
                                    $currentScreen['len'] = $optionValue;
                                else Colors::error("Has to be a number!");
                            //           Where id={}
                            } else if (strtolower($option) == 'id') {
                                if (is_numeric($optionValue))
                                    $currentScreen['where'] = "`id`=".$optionValue;
                                else unset($currentScreen["where"]);
                            //           where={query}
                            } else if (strtolower($option) == 'where') {
                                if ($optionValue != "")
                                    $currentScreen['where'] = $optionValue;
                                else unset($currentScreen['where']);
                            //           order
                            } else if (strtolower($option) == 'order') {
                                if ($optionValue != "")
                                    $currentScreen['order'] = $optionValue;
                                else unset($currentScreen['order']);
                            }
                        } else if ($input == '+') {
                            if (isset($currentScreen['page'])) $currentScreen['page'] += 1;
                        } else if ($input == '-') {
                            if (isset($currentScreen['page']) && $currentScreen['page'] != 0) $currentScreen['page'] -= 1;
                        }
                        $currentScreen = array_merge($currentScreen, [
                            "action" => "TABLE",
                            "name"   => $currentScreen['name'],
                            "query"  => "SELECT * FROM `".$currentScreen['name']."` "
                                .(isset($currentScreen['where']) ? " WHERE ".$currentScreen['where'] : "")      
                                .(isset($currentScreen['order']) ? " ORDER BY ".$currentScreen['order'] : "")
                                ." LIMIT ".(isset($currentScreen['limit']) ? $currentScreen['limit'] : "5") 
                                ." OFFSET ".(isset($currentScreen['page']) && isset($currentScreen['limit']) ? $currentScreen['limit']*$currentScreen['page'] : "0")
                                .";"
                        ]);
                    }
                } else if ($action == "QUERY") {
                    $currentScreen = [
                        "action" => "NONE",
                        "query"  => ""
                    ];
                    $action = "";
                } else if($action == 'SQL') {
                    if ( substr(trim($currentScreen['query']), -1) == ';') {
                        $currentScreen = [
                            "action" => "QUERY",
                            "query"  => $currentScreen["query"],
                            "removeAfter" => true
                        ];
                        readline_add_history("sql\n".$currentScreen["query"]);
                    } else if (substr(trim($currentScreen['query']), -1) == '\\') {
                        $currentScreen['query'] = rtrim($currentScreen['query'], "\\");
                    }
                }
            }
        }, "A simplified database-navigator");
    }

    private function responseTransformer($db, $sql, $tableAction = false){
        $query = $db->getConnection()->query($sql);
        $response = ["type" => "NONE", "statement" => $query];
        
        if ($query === false) {
            $response = ["type" => "request", "success" => false];
            return $response;
        }

        $resultSet = $query->fetchAll(\PDO::FETCH_OBJ);
        
        if ($query->rowCount() === 1 && count($resultSet) == 0 && !$tableAction) {
            $response = ["type" => "request", "success" => true];
        } else {
            $entries = $resultSet;
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