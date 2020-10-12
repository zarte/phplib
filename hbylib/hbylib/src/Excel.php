<?php
/**
 *
 *  表格导出类
 * Created by PhpStorm
 * User: aloner
 * Date: 2018/9/5 0005
 * Version: 1.0
 */
namespace Hbylib\Hbylib;

/**
 * Excel生成类
 *
 */
class Excel
{

    private $header ="<?xml version=\"1.0\" ?>\n
            <Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"
            xmlns:x=\"urn:schemas-microsoft-com:office:excel\"
            xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"
            xmlns:html=\"http://www.w3.org/TR/REC-html40\">";

    private $coding;
    private $type;
    private $tWorksheetTitle;
    private $filename;
    private $titleRow = [];
    private $outtype; //输出文档类型csv xls

    /**
     * Excel基础配置
     *
     * @param string $enCoding 编码
     * @param boolean $boolean 转换类型
     * @param string $title 表标题
     * @param string $filename Excel文件名
     *
     * @return void
     */
    public function config($enCoding,$boolean,$title,$filename,$outtype='csv')
    {
        //编码
        $this->coding = $enCoding;
        //转换类型
        if ($boolean == true){
            $this->type = 'Number';
        } else {
            $this->type = 'String';
        }
        $this->outtype = $outtype;
        //表标题
        $title = preg_replace('/[\\\|:|\/|\?|\*|\[|\]]/', '', $title);
        $title = substr ($title, 0, 30);
        $this->tWorksheetTitle=$title;
        //中文名称使用urlencode编码后在IE中打开能保存成中文名称的文件,但是在FF上却是乱码
       // $filename = preg_replace('/[^aA-zZ0-9\_\-]/', '', $filename);
        $this->filename = $filename;
    }

    /**
     * 添加标题行
     *
     * @param array $titleArr
     */
    public function setTitleRow($titleArr)
    {
        $this->titleRow = $titleArr;
    }

    /**
     * 循环生成Excel行
     *
     * @param array $data
     *
     * @return string
     */
    private function addRow($data)
    {
        $cells = '';
        foreach ($data as $val){
            $type = $this->type;
            //字符转换为 HTML 实体
            $val = htmlentities($val,ENT_COMPAT,$this->coding);
            $cells .= "<Cell><Data ss:Type=\"$type\">" . $val . "</Data></Cell>\n";
        }
        return $cells;
    }

    /**
     * 循环生成Excel行
     *
     * @param array $data
     *
     * @return string
     */
    private function addCRow($data)
    {
        $cells = '';
        foreach ($data as $val){
            //字符转换为 HTML 实体
       //     $val = htmlentities($val,ENT_COMPAT,$this->coding);
            $val = str_replace(',','，',$val);
            $val = iconv ('utf-8',$this->coding,$val);
            $cells .= $val.',';
        }
        trim($cells,',');
        return $cells;
    }

    public function excelCsv($data)
    {
        ob_end_clean();//清空（擦除）缓冲区并关闭输出缓冲
        ob_implicit_flush(true);//打开或关闭绝对（隐式）刷送
        header("Content-type:text/csv;charset=" . $this->coding);
        header("Content-Disposition:attachment;filename=".$this->filename.".csv");
        header("Content-Encoding: binary");
        header('Expires:0');
        header('Pragma:public');
        echo chr(0xEF).chr(0xBB).chr(0xBF);
        if (is_array($this->titleRow)) {
            echo $this->addCRow($this->titleRow);
        }
        foreach ($data as $val){
            $rows=$this->addCRow($val);
            echo "\n".$rows;
        }
        return true;
    }


    //大数据使用以下系列方法
    public function realouthead(){
        if($this->outtype=='csv'){
            ob_end_clean();//清空（擦除）缓冲区并关闭输出缓冲
            ob_implicit_flush(true);//打开或关闭绝对（隐式）刷送
            header("Content-type:text/csv;charset=" . $this->coding);
            header("Content-Disposition:attachment;filename=".$this->filename.".csv");
            header("Content-Encoding: binary");
            header('Expires:0');
            header('Pragma:public');
            echo chr(0xEF).chr(0xBB).chr(0xBF);
        }else{
            header("Content-type:application/octet-stream");
            header("Accept-Ranges:bytes");

            header("Content-Type: application/vnd.ms-excel; charset=" . $this->coding);
            header("Content-Disposition: inline; filename=\"" . $this->filename . ".xls\"");
            header("Pragma: no-cache");
            header("Expires: 0");
        }
    }
    public function realouttitile(){
        if($this->outtype=='csv'){
            if (is_array($this->titleRow)) {
                echo $this->addCRow($this->titleRow);
            }
        }else{
            echo stripslashes (sprintf($this->header, $this->coding));
            echo "\n<Worksheet ss:Name=\"" . $this->tWorksheetTitle . "\">\n<Table>\n";

            if (is_array($this->titleRow)) {
                echo "<Row>\n".$this->addRow($this->titleRow)."</Row>\n";
            }
        }
    }
    public function realoutrow($data){
        if($this->outtype=='csv'){
            $rows=$this->addCRow($data);
            echo "\n".$rows;
        }else{
            $rows=$this->addRow($data);
            echo "<Row>\n".$rows."</Row>\n";
        }
    }
    public function realoutend(){
        if($this->outtype=='csv'){
            exit();
        }else{
            echo "</Table>\n</Worksheet>\n";
            echo "</Workbook>";
            exit();
        }
    }




    /**
     * 生成Excel文件
     *
     * @param array $data
     *
     * @return void
     */
    public function excelXls($data)
    {
        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");

        header("Content-Type: application/vnd.ms-excel; charset=" . $this->coding);
        header("Content-Disposition: inline; filename=\"" . $this->filename . ".xls\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        /*打印*/
        echo stripslashes (sprintf($this->header, $this->coding));
        echo "\n<Worksheet ss:Name=\"" . $this->tWorksheetTitle . "\">\n<Table>\n";

        if (is_array($this->titleRow)) {
            echo "<Row>\n".$this->addRow($this->titleRow)."</Row>\n";
        }
        foreach ($data as $val){
            $rows=$this->addRow($val);
            echo "<Row>\n".$rows."</Row>\n";
        }
        echo "</Table>\n</Worksheet>\n";
        echo "</Workbook>";
        exit();
    }
}