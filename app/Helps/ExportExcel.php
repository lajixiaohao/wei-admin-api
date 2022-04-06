<?php
/**
 * 导出Excel助手封装
 * */
namespace App\Helps;

class ExportExcel
{
	/**
    * 使用自定义table标签、样式填充为完整的Html
    * @param string $tableHtml
    * @param string $styleHtml
    * @return string
    */
    public static function formatHtml($tableHtml = '', $styleHtml = '')
    {
        return '<!DOCTYPE html><html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><meta charset="utf-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name></x:Name><x:WorksheetOptions><x:Selected/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--><style type="text/css">'.$styleHtml.'</style></head><body>'.$tableHtml.'</body></html>';
    }
}
