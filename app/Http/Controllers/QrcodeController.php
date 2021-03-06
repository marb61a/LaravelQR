<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateQrcodeRequest;
use App\Http\Requests\UpdateQrcodeRequest;
use App\Repositories\QrcodeRepository;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Flash;
use QRCode;
use Auth;
use App\Models\Qrcode as QrcodeModel;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

class QrcodeController extends AppBaseController
{
    /** @var  QrcodeRepository */
    private $qrcodeRepository;

    public function __construct(QrcodeRepository $qrcodeRepo)
    {
        $this->qrcodeRepository = $qrcodeRepo;
    }

    /**
     * Display a listing of the Qrcode.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->qrcodeRepository->pushCriteria(new RequestCriteria($request));
        $qrcodes = $this->qrcodeRepository->all();

        return view('qrcodes.index')
            ->with('qrcodes', $qrcodes);
    }

    /**
     * Show the form for creating a new Qrcode.
     *
     * @return Response
     */
    public function create()
    {
        return view('qrcodes.create');
    }

    /**
     * Store a newly created Qrcode in storage.
     *
     * @param CreateQrcodeRequest $request
     *
     * @return Response
     */
    public function store(CreateQrcodeRequest $request)
    {
        $input = $request->all();

        // Save the code to the database
        $qrcode = $this->qrcodeRepository->create($input);

        // Generate QRCode
        // Save QRCode image in the folder on this site
        $file = 'generated_qrcodes/'.$qrcode->id.'png';

        $newQrcode = QRCode::text(route('qrcodes.show', $qrcode->id))
        ->setSize(4)
        ->setMargin(2)
        ->setOutFile($file)
        ->png();

        $input['qrcode_path'] = $file;

        //update database
        $newQrcode = QrcodeModel::where('id', $qrcode->id)->update([
                            'qrcode_path' => $input['qrcode_path']
                        ]);

        if($newQrcode){
            $getQrcode =  QrcodeModel::where('id', $qrcode->id)->first();

            // Check if the response expects JSON
            if($request->expectsJson()){
                return response([
                    'data' => new QrcodeResource($getQrcode)
                ], Response::HTTP_CREATED); 
            }  

            Flash::success('Qrcode saved successfully.');
        
        } else {
            Flash::errors('Qrcode was not saved.');
        }

        return redirect(route('qrcodes.show', ['qrcode' => $qrcode]));
    }

    /**
     * Display the specified Qrcode.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id, Request $request)
    {
        $qrcode = $this->qrcodeRepository->findWithoutFail($id);

        if (empty($qrcode)) {
            if($request->expectsJson()){
                 throw new \ErrorException();
            }
            
            Flash::error('Qrcode not found');
            return redirect(route('qrcodes.index'));
        }

        $transactions = $qrcode->transactions;

        if ($request->expectsJson()) {
            return response([
                'data' => new QrcodeResource($qrcode)
            ], Response::HTTP_OK); 
        }  
        
        return view('qrcodes.show')
        ->with('transactions', $transactions)
        ->with('qrcode', $qrcode);
    }

    /**
     * Show the form for editing the specified Qrcode.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $qrcode = $this->qrcodeRepository->findWithoutFail($id);

        if (empty($qrcode)) {
            Flash::error('Qrcode not found');

            return redirect(route('qrcodes.index'));
        }

        return view('qrcodes.edit')->with('qrcode', $qrcode);
    }

    /**
     * Update the specified Qrcode in storage.
     *
     * @param  int              $id
     * @param UpdateQrcodeRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateQrcodeRequest $request)
    {
        $qrcode = $this->qrcodeRepository->findWithoutFail($id);

        if (empty($qrcode)) {
            Flash::error('Qrcode not found');

            return redirect(route('qrcodes.index'));
        }

        $qrcode = $this->qrcodeRepository->update($request->all(), $id);

        Flash::success('Qrcode updated successfully.');

        return redirect(route('qrcodes.index'));
    }

    /**
     * Remove the specified Qrcode from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $qrcode = $this->qrcodeRepository->findWithoutFail($id);

        if (empty($qrcode)) {
            Flash::error('Qrcode not found');

            return redirect(route('qrcodes.index'));
        }

        $this->qrcodeRepository->delete($id);

        Flash::success('Qrcode deleted successfully.');

        return redirect(route('qrcodes.index'));
    }
}
