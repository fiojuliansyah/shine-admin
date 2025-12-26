<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\Career;
use App\Models\Company;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\DataTables\CareersDataTable;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CareerController extends Controller
{

    public function index(CareersDataTable $dataTable)
    {
        $companies = Company::all();
        return $dataTable->render('careers.index',compact('companies'));
    }

    public function show()
    {
        return view('careers.index');
    }

    public function store(Request $request)
    {
        $user = Auth::user()->id;

        $career = new Career;
        $career->status = 'hide';
        $career->company_id = $request->company_id;
        $career->name = $request->name;
        $career->slug = Str::slug($request->name);
        $career->description = $request->description;
        $career->department = $request->department;
        $career->location = $request->location;
        $career->workfunction = $request->workfunction;
        $career->experience = $request->experience;
        $career->graduate = $request->graduate;
        $career->major = $request->major;
        $career->salary = $request->salary;
        $career->candidate = $request->candidate;
        $career->until_date = $request->until_date;
        $career->user_id = $user;
        $career->save();

        $qrLink = route('web-career-detail', ['id' => encrypt($career->id)]);
        $qrCode = QrCode::size(200)->generate($qrLink);
        $career->qr_link = $qrCode;
        $career->save();

        return redirect()->route('careers.index')
                        ->with('success', 'Lowongan ' . $career->name . ' berhasil dibuat');
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user()->id;
        
        $career = Career::findOrFail($id);
        $career->status = $request->status;
        $career->company_id = $request->company_id;
        $career->name = $request->name;
        $career->description = $request->description;
        $career->department = $request->department;
        $career->location = $request->location;
        $career->workfunction = $request->workfunction;
        $career->experience = $request->experience;
        $career->graduate = $request->graduate;
        $career->major = $request->major;
        $career->salary = $request->salary;
        $career->candidate = $request->candidate;
        $career->until_date = $request->until_date;
        $career->user_id = $user;
        $career->update();

        return redirect()->route('careers.index')
                        ->with('success', 'Lowongan ' . $career->name . ' berhasil diperbarui');
    }


    public function destroy($id)
    {
        $career = Career::findOrFail($id);
        $career->delete();

        return redirect()->route('careers.index')
                        ->with('success', 'Lowongan berhasil dihapus');
    }

    public function updateStatus(Request $request, $id)
    {
        $career = Career::findOrFail($id);

        $career->status = $request->status;
        $career->update();

        return redirect()->back()
                        ->with('success', 'Lowongan ' . $career->name . ' berhasil diperbarui');
    }

    public function banner($id)
    {
        $ID = decrypt($id);
        $career = Career::findOrFail($ID);

        return view('careers.banner',compact('career'));;
    }
}
