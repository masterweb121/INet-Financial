<?php namespace VM\Financial\Components;

use Cms\Classes\ComponentBase;
use VM\Financial\Models\Remboursement as Rembdata;
use VM\Financial\Models\Category as Categories;
use VM\Financial\Models\ValidationProcess as ValidationProcess;
use RainLab\User\Models\Settings as UserSettings;
use RainLab\User\Models\User as User;
use VM\Financial\Models\Member as RembUser;
use System\Models\File as FileUpload;
use Flash;
use Auth;
use Redirect;
use Input;
class Remboursement extends ComponentBase
{

    /**
     * Catégories de payement
     * @var array
     */
    public $categories;

    /**
     * Validation process list
     * @var array
     */
    public $validationprocess;

    public function componentDetails()
    {
        return [
            'name'        => 'Formulaire de remboursement',
            'description' => 'Affiche un formulaire pour créer un nouveau remboursement'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->addCss('assets/css/bootstrap.css');
        $this->addJs('assets/js/bootstrap.js');
        $this->addJs('assets/js/jquery.min.js');
        $this->setVariables();

    }
    public function setVariables()
    {
        $this->categories = Categories::all();
        $this->validationprocess = ValidationProcess::all();
    }
    public function onAddRemb()
    {
        $description = post('description');
        $username = post('username');
        $email = post('email');
        $address = post('address');
        $npa = post('npa');
        $city = post('city');
        $ccp = post('ccp');
        echo $description;
        $catid = post('category');
        $processid = post('validationprocess');
        $currentRemb = new Rembdata;
        $currentRemb->description = $description;

        // Associate and create user through member model
        $currentRemb->remb_user = $this->userExistOrCreate($email);

        // associate Validation process
        $validationprocess = ValidationProcess::find($processid);
        $currentRemb->validation_process = $validationprocess;

        // associate category
        $category = Categories::find($catid);
        $currentRemb->category = $category;

        // Pièce jointe
        $file = new FileUpload;
        $file->data = Input::file('justificatifs');
        $file->save();
        $currentRemb->save();
        $currentRemb->justificatifs()->add($file);
        $currentRemb->save();
        // Save Remboursement


    }
    /**
     * Check if a user exist (by checking email)
     * @param string $email user's email
     * @return Member
     */
    function userExistOrCreate($email)
    {
        $username = post('username');
        $address = post('address');
        $npa = post('npa');
        $city = post('city');
        $ccp = post('ccp');

        // Check if user exist in Remboursement User model
        //
        $rb_user = RembUser::where('email', '=', $email)->first();
        $user = User::where('email', '=', $email)->first();
        if(!is_null($rb_user) && !is_null($user)){
            return $rb_user;
        }
        if(is_null($rb_user)){
            #TODO update field if changed
            $rembUser = new RembUser;
            $rembUser->username = $username;
            $rembUser->email = $email;
            $rembUser->ccp = $ccp;
            $rembUser->npa = $npa;
            $rembUser->city = $city;
            $rembUser->address = $address;

        }
        if(is_null($user)){
            /*
             * Register user
             */
             $password = str_random(20);
             $data = [ //data array which contain email & a random pass
                 'email' => $email,
                 'password' => $password,
                 'password_confirmation' => $password
             ];

            $requireActivation = UserSettings::get('require_activation', true);
            $automaticActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_AUTO;
            $userActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_USER;
            $user = Auth::register($data, $automaticActivation);
            Auth::login($user);
            $user = User::where('email', '=', $email)->first();
        }

        $rembUser->user = $user;
        $rembUser->save();
        return $rembUser;
    }

}
