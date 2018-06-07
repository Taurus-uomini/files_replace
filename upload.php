<?php

    function decompressing_file($files)
    {
        if($files['type'] == 'application/zip' || $files['type'] == 'application/x-zip-compressed')
        {
            $zip = new zipArchive();
            if($zip->open($files['tmp_name']) === true)
            {
                $filepath = md5(time().$files['name']);
                mkdir($filepath);
                for($i=0;$i<$zip->numFiles;++$i)
                {
                    $filename = $zip->getNameIndex($i);
                    $fileinfo = pathinfo($filename);
                    if(!file_exists($filepath.'/'.$fileinfo['dirname']))
                    {
                        mkdir($filepath.'/'.$fileinfo['dirname']);
                    }
                    if($filename[strlen($filename)-1]!='/')
                    {
                        copy('zip://'.$files['tmp_name'].'#'.$filename,$filepath.'/'.$fileinfo['dirname'].'/'.$fileinfo['basename']);
                    }
                }
                $zip->close();
                return $filepath;
            }
        }
        return null;
    }

    function files_replace($frompath,$topath)
    {
        if(!file_exists($topath))
        {
            mkdir($topath);
        }
        $files = scandir($frompath);
        for($i=0;$i<count($files);++$i)
        {
            if($files[$i]=='.'||$files[$i]=='..')
            {
                continue;
            }
            if(is_dir($frompath.'/'.$files[$i]))
            {
                files_replace($frompath.'/'.$files[$i],$topath.'/'.$files[$i]);
            }
            else
            {
                $needtoreplace = true;
                if(file_exists($topath.'/'.$files[$i]))
                {
                    if(filemtime($frompath.'/'.$files[$i]) == filemtime($topath.'/'.$files[$i]))
                    {
                        $needtoreplace = false;
                    }
                    else
                    {
                        $oldfilehash = hash_file('md5',$frompath.'/'.$files[$i]);
                        $newfilehash = hash_file('md5',$topath.'/'.$files[$i]);
                        if($oldfilehash == $newfilehash)
                        {
                            $needtoreplace = false;
                        }
                    }
                }
                if($needtoreplace)
                {
                    copy($frompath.'/'.$files[$i],$topath.'/'.$files[$i]);
                    echo('replacefile:'.$topath.'/'.$files[$i]."<br/>");
                }
            }
        }
    }

    function delete_files($filepath)
    {
        $files = scandir($filepath);
        for($i=0;$i<count($files);++$i)
        {
            if($files[$i]=='.'||$files[$i]=='..')
            {
                continue;
            }
            if(is_dir($filepath.'/'.$files[$i]))
            {
                delete_files($filepath.'/'.$files[$i]);
            }
            else
            {
                unlink($filepath.'/'.$files[$i]);
            }
        }
        rmdir($filepath);
    }
    $files = $_FILES['files'];
    if($files['type']!='application/zip' && $files['type']!='application/x-zip-compressed')
    {
        echo "只能是zip压缩文件";
        exit();
    }
    $filepath = decompressing_file($files);
    if($filepath)
    {
        files_replace($filepath,'code');
        delete_files($filepath);
        echo "文件替换成功";
    }
    else
    {
        echo "文件解压失败";
    }
