<?php
/*
 * Export data as a CSV file.
 *
 * @param        $data
 * @param string $file_name
 */
if (!function_exists('csvExport')) {
    function csvExport($data, string $file_name)
    {
        header('HTTP/1.1 200 OK');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Date: '.date('D M j G:i:s T Y'));
        header('Last-Modified: '.date('D M j G:i:s T Y'));
        header('Content-Type: application/vnd.ms-excel');
//    header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"${file_name}\"");
        die(is_string($data) ? $data : (is_array($data) ? arrayToCsv($data) : $data));
    }
}

/*
 * Convert the given array into a CSV formatted string.
 *
 * @param array $data
 * @return string
 */
if (!function_exists('arrayToCsv')) {
    function arrayToCsv(array $data): string
    {
        $ret = '';

        foreach ($data as $row) {
            foreach ($row as $column) {
                if (is_numeric($column)) {
                    $ret .= "${column},";
                } elseif (is_string($column)) {
                    $ret .= "\"${column}\",";
                } else {
                    $ret .= "${column},";
                }
            }
            $ret = trim($ret, ',')."\n";
        }

        return $ret;
    }
}