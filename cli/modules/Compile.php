<?php
namespace modules\ulole\cli\modules;

class Compile {
    
    public static function compileDir($config, $indir) {
        $conf = json_decode(file_get_contents($config));
        foreach ($conf as $val => $key) {
            $out = "";
            $forout = "";
            foreach ($key as $file) {
                 $out .= file_get_contents($indir."/".$file);
            }
            file_put_contents("public/".$val, $out);
        }
    }

    public static function compileViews($dir, $enddir, $output = true) {
        $replaceArray = [
            "{[{"=>'<?php echo htmlspecialchars(',
            "}]}"=>'); ?>',
            "{{"=>'<?php echo (',
            "}}"=>'); ?>',
            
            // STARTS
            "@if(("=>'<?php if(',
            "@elseif(("=>'<?php elseif(',
            "@foreach(("=>'<?php foreach(',
            "@for(("=>'<?php for(',
            "@while(("=>'<?php while(',
            
            "@import(("=>"<?php import(",
            "@view(("=>"<?php modules\\deverm\\Router::view(",
            "@component(("=>"<?php modules\\deverm\\Router::view(",
            "@comp(("=>"<?php modules\\deverm\\Router::view(",
            "@template(("=>"<?php modules\\deverm\\Router::tmpl(",

            "))!"=>"); ?>",
            "))#"=>"):?>",
            '@else'=>"<?php else: ?>",
            
            // ENDS
            '@endif'=>"<?php endif; ?>",
            '@endforeach'=>"<?php endforeach; ?>",
            '@endwhile'=>"<?php endwhile; ?>",
            
            '<!#--'=>"<?php /*",
            '--#>'=>"*/?>",

            '<?#'=>'<?php',
            '#?>'=>'?>'
        ];

        $files = scandir($dir);
        foreach($files as $file) {
            if ($file != ".." && $file != ".") {
                if (is_dir($dir."/".$file)){
                    if(!\is_dir($enddir."/".$file))
                        \mkdir($enddir."/".$file);
                    self::compileViews($dir."/".$file, $enddir."/".$file, $output);
                } elseif (strpos($file, "view.php")) {
                    \file_put_contents($enddir."/".str_replace(".view.php", ".php", $file), self::replaceByArray($replaceArray, \file_get_contents($dir."/".$file)));
                    if ($output)
                        echo "\nview.php renderer: rendered: ".$enddir.$dir."/".$file." into ".$enddir."/".str_replace(".view.php", ".php", $file);
                }
            }        
        }
    } 

    public static function replaceByArray( array $array, string $string ){
        foreach ($array as $key=>$val)
            $string = str_replace($key, $val,$string);

        return $string;
    }
}