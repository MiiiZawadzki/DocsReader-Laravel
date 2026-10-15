<?php

namespace Modules\Analytics\Http\Requests;

use App\Concerns\AuthorizesPermissions;
use Illuminate\Foundation\Http\FormRequest;

class ManageStatisticsRequest extends FormRequest
{
    use AuthorizesPermissions;

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->userHasPermission('manage-documents');
    }
}
