<?php namespace VM\Financial\Components;

use Cms\Classes\ComponentBase;
use VM\Financial\Models\Remboursement as Rembdata;

class Moderate extends ComponentBase
{
    /**
     * Listes des payements à modérer
     * @var array
     */
    public $paymentsModerated;

    /**
     * Listes des payements à effectuer
     * @var array
     */
    public $paymentsPayed;

    /**
     * Lien pour la page de payement
     * @var string Reference to the page name for linking to channels.
     */
    public $paymentPage;

    public function componentDetails()
    {
        return [
            'name'        => 'Affiche les payements à modérer',
            'description' => 'Affiche les payements à modérer (validator)'
        ];
    }

    public function defineProperties()
    {
        return [
            'paymentPage' => [
                'title'       => 'paymentPage',
                'description' => 'Page de payement avec slug',
                'type'        => 'string',
            ],
        ];
    }

    /**
     * Lauch on page run, can't run AJAX
     */
    public function onRun()
    {
        $this->setVariables();
        $this->addCss('assets/css/main.css');
    }
    public function setVariables()
    {
        $this->paymentsModerated = Rembdata::all();
        #$this->paymentPage = $this->property('paymentPage');
        #$this->paymentsModerated->each(function($model) {
        #    $model->justificatifs = $model->justificatifs->output();
        #});


        #echo $model->avatar->getPath();
    }

    public function onAttachment(){
        echo $attached_file->justificatifs->output();
    }

    public function updateDefault()
    {
        $this->page['payments'] = Rembdata::all();
        return ['#default_container' => $this->renderPartial('list_payments')];
    }

    /**
     * Change payment's status from 'new' to 'moderated'
     */
    public function onAccept()
    {
        $ValidatedRemb = Rembdata::find(post('id'));
        $ValidatedRemb->status = 'moderated';
        $ValidatedRemb->save();
        $this->paymentsModerated = Rembdata::all();
    }
    /**
     * Ask for more details about a payment by sending an email to the payment's owner
     */
    public function onAskMore(){
        $ValidatedRemb = Rembdata::find(post('id'));
        $ValidatedRemb->status = 'hold';
        $ValidatedRemb->save();
        $this->paymentsModerated = Rembdata::all();

    }
}
