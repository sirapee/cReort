<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class UserDetailsExport implements FromQuery , WithHeadings
{
    use Exportable;
    public function query()
    {
        return User::query()
            ->select('username','email','emp_id','first_name','last_name',
                'department','job_title','last_login','deleted_at', DB::raw("CASE WHEN deleted_at is null then 'Inactive' else 'Active' end AS status"),
                'created_at','created_by',
                'two_factor');
    }

    public function headings(): array
    {
        return [
            'Username','Email','Employee Id','First Name','Last Name',
            'Department','Job Title','Last Login','Deleted At', 'Status',
            'Created At','Created By',
            'Two Factor'
        ];
    }
}
