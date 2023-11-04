<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Training;
use Illuminate\Support\Facades\File;
use App\Models\TrainingRegistration;
class TrainingRegistrationController extends Controller
{
    public function getTrainingList(){
        $trainings = Training::all();
        return view('user.training-list',compact('trainings'));
    }

    public function getRegistrationForm(String $id){
        $training = Training::find($id);
        return view('user.registration-form',compact('training'));
    }


    public function postRegisterTraining(Request $request){

        $trainingRegistration = new TrainingRegistration();
        $user = auth()->user();
        $userId = auth()->user()->id;
        $this->validate($request, [
            'training_id' => 'required|exists:trainings,id',
            'proofofpayment' => 'required|mimes:jpeg,jpg,png,pdf|max:10000',

        ]);

        $trainingRegistration->user_id = $user->id;
        $trainingRegistration->training_id = $request['training_id'];

        if($request->hasFile('proofofpayment')){
          
            $proof_name = $request->file('proofofpayment');
            $filename = 'proof_'.$userId.'_'.time().'.'.$proof_name->getClientOriginalExtension();

            // Ensure the directory exists
            $path = public_path('uploads/proof');
            if(!File::isDirectory($path)){
                File::makeDirectory($path, 0777, true, true);
            }

            $file = $request['proofofpayment'];
            $file->move($path, $filename);
            $trainingRegistration->proofofpayment = $filename;
        }

        

        $trainingRegistration->save();

        Session()->flash('message', 'Your registration has been successfully submitted');

        return redirect()->route('user.training-list');

    }

public function getRegisteredTraining(){
    $user = auth()->user(); 
    $trainingRegistrations = TrainingRegistration::where('user_id',$user->id)->get();
    return view('user.user-dashboard',compact('trainingRegistrations'));
}

public function getManageRegistration(){
    $trainingRegistrations = TrainingRegistration::all();
    return view('admin.manage-registration',compact('trainingRegistrations'));
}

public function patchEditRegistrationStatus(Request $request,string $id){
    $trainingRegistrations = TrainingRegistration::find($id);
    $this->validate($request,[
        'submit' =>'required | in:approve,reject',
    ]);
    if($request['submit']=='approve'){
        $trainingRegistrations->status = 1;
    }
    else{
        $trainingRegistrations->status = 0;
    }
    $trainingRegistrations->save();
    Session()->flash('message', 'Status updated successfully');
    return redirect()->route('admin.manage-registration');

}

}
