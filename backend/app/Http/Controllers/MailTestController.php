<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMail;

class MailTestController extends Controller
{
    public function receiveContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'message' => 'required|string',
        ]);

        $userName = $request->input('name');
        $userEmail = $request->input('email');
        $userMessage = $request->input('message');

        $adminEmail = env('MAIL_FROM_ADDRESS'); 

        try {
            Mail::to($adminEmail)->send(new ContactMail($userMessage, $userEmail, $userName));
            
            return response()->json([
                'status' => 'success',
                'message' => 'Gracias por tu reporte. El correo ha sido enviado con éxito'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hubo un error al enviar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }
}
