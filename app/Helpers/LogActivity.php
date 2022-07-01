<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 15/09/2017
 * Time: 12:51
 */
namespace App\Helpers;
use Request;
use App\Models\LogActivity as LogActivityModel;
use Sentinel;

class LogActivity
{

    public static function addToLog($subject,$details = ' ')
    {
        $log = [];
        $log['subject'] = $subject;
        $log['url'] = Request::fullUrl();
        $log['method'] = Request::method();
        $log['ip'] = Request::ip();
        $log['agent'] = Request::header('user-agent');
        $log['details'] = $details;
        $log['user_id'] = Sentinel::check() ? Sentinel::getUser()->emp_id : 'SYSTEM';
        $log['name'] = Sentinel::check() ? Sentinel::getUser()->first_name. ' '.Sentinel::getUser()->first_name : 'SYSTEM';
        LogActivityModel::create($log);
    }

    public static function logActivityLists()
    {
        return LogActivityModel::latest()->paginate(10);
    }

    public static function logActivitySearch($constraints)
    {
        return LogActivityModel::where('created_at', '>=', $constraints['from'])
            ->where('created_at', '<=', $constraints['to'])
            ->paginate(5);
    }

}
