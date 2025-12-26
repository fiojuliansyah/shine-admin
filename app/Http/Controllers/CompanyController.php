<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\Company;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\DataTables\CompaniesDataTable;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class CompanyController extends Controller
{
    public function index(CompaniesDataTable $dataTable)
    {
        $title = 'Manage Company';
        return $dataTable->render('companies.index', compact('title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'logo' => 'max:3000',
        ]); 
        
        $cloudinaryImage = $request->file('logo')->storeOnCloudinary('companies_logo');
        $url = $cloudinaryImage->getSecurePath();
        $public_id = $cloudinaryImage->getPublicId();

        $lastCompany = Company::orderBy('id', 'desc')->first();
        $nextId = $lastCompany ? $lastCompany->id + 1 : 1;
        $uniqueId = 'CMP-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

        $isFirstCompany = Company::count() === 0;
    
        $company = new Company;
        $company->name = $request->name;
        $company->unique_id = $uniqueId;
        $company->short_name = $request->short_name;
        $company->logo_url = $url;
        $company->logo_public_id = $public_id;
        $company->is_default = $isFirstCompany ? 1 : ($request->is_default ?? null);
        $company->save();

        return redirect()->route('companies.index')
                        ->with('success', 'Perusahaan ' . $company->name . ' berhasil dibuat');
    }

    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);

        if($request->hasFile('logo')){
            Cloudinary::destroy($company->logo_public_id);
            $cloudinaryImage = $request->file('logo')->storeOnCloudinary('companies_logo');
            $url = $cloudinaryImage->getSecurePath();
            $public_id = $cloudinaryImage->getPublicId();

            $company->update([
                'logo_url' => $url,
                'logo_public_id' => $public_id,
            ]);

        }
    
        $company->update([
            'name' => $request->name,
            'short_name' => $request->short_name,
            'is_default' => $request->is_default
        ]);
    
        return redirect()->route('companies.index')
            ->with('success', 'Data perusahaan berhasil diperbarui');
    }

    public function destroy($id)
    {
        $company = Company::findOrFail($id);
    
        Cloudinary::destroy($company->logo_public_id);
        $company->delete();
    
        return redirect()->route('companies.index')
            ->with('success', 'Data perusahaan berhasil dihapus');
    }
}
