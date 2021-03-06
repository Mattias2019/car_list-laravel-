<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Permissions;
use App\User;
use App\Car;
use App\Container;
use App\WebmasterSection;
use Auth;
use File;
use Illuminate\Config;
use Illuminate\Http\Request;
use Redirect;

class MainController extends Controller
{

    private $uploadPath = "uploads/containers/";

    // Define Default Variables

    public function __construct()
    {
        $this->middleware('auth');

        // Check Permissions
        if (@Auth::user()->permissions != 0 && Auth::user()->permissions != 1) {
            return Redirect::to(route('NoPermission'))->send();
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        // General for all pages
        // $GeneralWebmasterSections = WebmasterSection::where('status', '=', '1')->orderby('row_no', 'asc')->get();
        // General END

        // fixable part
        $Cars = Car::where('user_id', '=', Auth::user()->id)->orderby('id','asc')->paginate(env('BACKEND_PAGINATION'));
        $Users = User::orderby('id', 'asc')->paginate(env('BACKEND_PAGINATION'));
        $Containers = Container::orderby('id', 'asc')->paginate(env('BACKEND_PAGINATION'));
        $Permissions = Permissions::orderby('id', 'asc')->get();
        return view("backEnd.maindetails", compact("Containers","Permissions", "Users","Cars"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        // General for all pages
        $GeneralWebmasterSections = WebmasterSection::where('status', '=', '1')->orderby('row_no', 'asc')->get();
        // General END
        $Cars = Car::orderby('id' ,'asc')->get();
        $Users = User::orderby('id', 'asc')->get();

        return view("backEnd.containers.create", compact("GeneralWebmasterSections", "Users","Cars"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $this->validate($request, [
            'image' => 'mimes:png,jpeg,jpg,gif|max:3000',
            'number' => 'required',
            // 'status' => 'required|email|unique:users',
            'details' => 'required',
            'shipping_line' => 'required',
            'status' =>'required'

        ]);


        // Start of Upload Files
        $formFileName = "image";
        $fileFinalName_ar = "";
        if ($request->$formFileName != "") {
            $fileFinalName_ar = time() . rand(1111,
                    9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
            $path = base_path() . "/public/" . $this->getUploadPath();
            $request->file($formFileName)->move($path, $fileFinalName_ar);
        }
        // End of Upload Files
       
        $Container = new container;
        $Container->number = $request->number;
        $Container->details = $request->details;
        $Container->users = $request->user_id;
        $Container->cars = $request->car_id;
        $Container->shipping_line = $request->shipping_line;
        $Container->dates = $request->dates;
        $Container->status = $request->status;
        $Container->image = $fileFinalName_ar;
         $Container->save();

        return redirect()->action('ContainersController@index')->with('doneMessage', trans('backLang.addDone'));
    }

    public function getUploadPath()
    {
        return $this->uploadPath;
    }

    public function setUploadPath($uploadPath)
    {
        $this->uploadPath = Config::get('app.APP_URL') . $uploadPath;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        // General for all pages
        // $GeneralWebmasterSections = WebmasterSection::where('status', '=', '1')->orderby('row_no', 'asc')->get();
        // General END
        $Users = User::orderby('id', 'asc')->get();
        $Cars = Car::orderby('id','asc')->get();
         if (@Auth::user()->permissionsGroup->view_status) {
            $Users = User::where('created_by', '=', Auth::user()->id)->orwhere('id', '=', Auth::user()->id)->find($id);
        } else {
            $Containers = Container::find($id);
        }
        if (count($Containers) > 0) {
            return view("backEnd.containers.edit", compact("Containers", "Cars","Users", "GeneralWebmasterSections"));
        } else {
            return redirect()->action('ContainersController@index');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $Container = Container::find($id);
        if (count($Container) > 0) {


            $this->validate($request, [
                'image' => 'mimes:png,jpeg,jpg,gif|max:3000',
                'number' => 'required',
                'dates' => 'required',
                'details' => 'required',     
                'status' =>'required'
            ]);

            
            // Start of Upload Files
            $formFileName = "image";
            $fileFinalName_ar = "";
            if ($request->$formFileName != "") {
                $fileFinalName_ar = time() . rand(1111,
                        9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
                $path = base_path() . "/public/" . $this->getUploadPath();
                $request->file($formFileName)->move($path, $fileFinalName_ar);
            }
            // End of Upload Files
            //if ($id != 1) {
                $Container->number = $request->number;
                $Container->details = $request->details;
                $Container->users = $request->user_id;
                $Container->cars = $request->car_id;
                $Container->shipping_line = $request->shipping_line;
                $Container->dates = $request->dates;
                $Container->status = $request->status;
                $Container->image = $fileFinalName_ar;
              
               
            //}
            // if ($request->image_delete == 1) {
            //     // Delete a User file
            //     if ($Container->image != "") {
            //         File::delete($this->getUploadPath() . $Container->image);
            //     }

            //     $Container->image = "";
            // }
            // if ($fileFinalName_ar != "") {
            //     // Delete a User file
            //     if ($Container->image != "") {
            //         File::delete($this->getUploadPath() . $Container->image);
            //     }

            //     $Container->image = $fileFinalName_ar;
            // }

            // $Container->image = $fileFinalName_ar;

            $Container->save();
            return redirect()->action('ContainersController@edit', $id)->with('doneMessage', trans('backLang.saveDone'));
        } else {
            return redirect()->action('ContainersController@index');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        if (@Auth::user()->permissionsGroup->view_status) {
            $User = User::where('created_by', '=', Auth::user()->id)->find($id);
        } else {
            $Container = Container::find($id);
        }
        if (count($Container) > 0 && $id != 1) {
            // Delete a User photo
            if ($Container->image != "") {
                File::delete($this->getUploadPath() . $Container->image);
            }

            $Container->delete();
            return redirect()->action('ContainersController@index')->with('doneMessage', trans('backLang.deleteDone'));
        } else {
            return redirect()->action('ContainersController@index');
        }
    }


    /**
     * Update all selected resources in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  buttonNames , array $ids[]
     * @return \Illuminate\Http\Response
     */
    public function updateAll(Request $request)
    {
        //
      if ($request->action == "delete") {
            // Delete User photo
            $Containers = Container::wherein('id', $request->ids)->where('id', '!=', 1)->get();
            foreach ($Containers as $Container) {
                if ($Container->image != "") {
                    File::delete($this->getUploadPath() . $Container->image);
                }
            }

            Container::wherein('id', $request->ids)->where('id', "!=", 1)
                ->delete();

        }
        return redirect()->action('ContainersController@index')->with('doneMessage', trans('backLang.saveDone'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function permissions_create()
    {
        //
        // General for all pages
        $GeneralWebmasterSections = WebmasterSection::where('status', '=', '1')->orderby('row_no', 'asc')->get();
        // General END

        return view("backEnd.users.permissions.create", compact("GeneralWebmasterSections"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function permissions_store(Request $request)
    {
        //
        $this->validate($request, [
            'name' => 'required'
        ]);

        $data_sections_values = "";
        if (@$request->data_sections != "") {
            foreach ($request->data_sections as $key => $val) {
                $data_sections_values = $val . "," . $data_sections_values;
            }
            $data_sections_values = substr($data_sections_values, 0, -1);
        }

        $Permissions = new Permissions;
        $Permissions->name = $request->name;
        $Permissions->view_status = ($request->view_status) ? "1" : "0";
        $Permissions->add_status = ($request->add_status) ? "1" : "0";
        $Permissions->edit_status = ($request->edit_status) ? "1" : "0";
        $Permissions->delete_status = ($request->delete_status) ? "1" : "0";
        $Permissions->analytics_status = ($request->analytics_status) ? "1" : "0";
        $Permissions->inbox_status = ($request->inbox_status) ? "1" : "0";
        $Permissions->newsletter_status = ($request->newsletter_status) ? "1" : "0";
        $Permissions->calendar_status = ($request->calendar_status) ? "1" : "0";
        $Permissions->banners_status = ($request->banners_status) ? "1" : "0";
        $Permissions->settings_status = ($request->settings_status) ? "1" : "0";
        $Permissions->webmaster_status = ($request->webmaster_status) ? "1" : "0";
        $Permissions->data_sections = $data_sections_values;
        $Permissions->status = true;
        $Permissions->save();

        return redirect()->action('UsersController@index')->with('doneMessage', trans('backLang.addDone'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function permissions_edit($id)
    {
        //
        // General for all pages
        $GeneralWebmasterSections = WebmasterSection::where('status', '=', '1')->orderby('row_no', 'asc')->get();
        // General END

        if (@Auth::user()->permissionsGroup->view_status) {
            $Permissions = Permissions::where('created_by', '=', Auth::user()->id)->find($id);
        } else {
            $Permissions = Permissions::find($id);
        }
        if (count($Permissions) > 0) {
            return view("backEnd.users.permissions.edit", compact("Permissions", "GeneralWebmasterSections"));
        } else {
            return redirect()->action('UsersController@index');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function permissions_update(Request $request, $id)
    {
        //
        $Permissions = Permissions::find($id);
        if (count($Permissions) > 0) {


            $this->validate($request, [
                'name' => 'required'
            ]);

            $data_sections_values = "";
            if (@$request->data_sections != "") {
                foreach ($request->data_sections as $key => $val) {
                    $data_sections_values = $val . "," . $data_sections_values;
                }
                $data_sections_values = substr($data_sections_values, 0, -1);
            }

            $Permissions->name = $request->name;
            $Permissions->view_status = ($request->view_status) ? "1" : "0";
            $Permissions->add_status = ($request->add_status) ? "1" : "0";
            $Permissions->edit_status = ($request->edit_status) ? "1" : "0";
            $Permissions->delete_status = ($request->delete_status) ? "1" : "0";
            $Permissions->analytics_status = ($request->analytics_status) ? "1" : "0";
            $Permissions->inbox_status = ($request->inbox_status) ? "1" : "0";
            $Permissions->newsletter_status = ($request->newsletter_status) ? "1" : "0";
            $Permissions->calendar_status = ($request->calendar_status) ? "1" : "0";
            $Permissions->banners_status = ($request->banners_status) ? "1" : "0";
            $Permissions->settings_status = ($request->settings_status) ? "1" : "0";
            $Permissions->webmaster_status = ($request->webmaster_status) ? "1" : "0";
            $Permissions->data_sections = $data_sections_values;
            $Permissions->status = $request->status;
            if ($id != 1) {
                $Permissions->save();
            }
            return redirect()->action('UsersController@permissions_edit', $id)->with('doneMessage',
                trans('backLang.saveDone'));
        } else {
            return redirect()->action('UsersController@index');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function permissions_destroy($id)
    {
        //
        if (@Auth::user()->permissionsGroup->view_status) {
            $Permissions = Permissions::where('created_by', '=', Auth::user()->id)->find($id);
        } else {
            $Permissions = Permissions::find($id);
        }
        if (count($Permissions) > 0 && $id != 1) {

            $Permissions->delete();
            return redirect()->action('UsersController@index')->with('doneMessage', trans('backLang.deleteDone'));
        } else {
            return redirect()->action('UsersController@index');
        }
    }


}
