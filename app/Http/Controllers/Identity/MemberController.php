<?php

namespace App\Http\Controllers\Identity;

use App\Actions\Identity\AddInstitutionMember;
use App\Http\Controllers\Controller;
use App\Http\Requests\Identity\MemberRequest;
use App\Models\Institution;
use Illuminate\Http\RedirectResponse;

class MemberController extends Controller
{
    public function __invoke(MemberRequest $request, Institution $institution, AddInstitutionMember $action): RedirectResponse
    {
        $action->execute($request->user(), $institution, $request->validated('email'));

        return to_route('accesos.show', $institution)->with('status', 'Membresía registrada. Los roles se asignan por separado.');
    }
}
