<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceInvoicesRequest;
use App\Http\Resources\ProductInvoiceResource;
use App\interfaces\Sorter;
use Illuminate\Http\Request;
use App\Models\ProductInvoice;
use App\Interfaces\CheckInvoiceFormat;
use App\Http\Resources\ServiceInvoiceCollection;
use App\Http\Resources\ServiceInvoiceResource;
use App\Models\ServiceInvoice;

class ServiceInvoiceController extends Controller implements Sorter, CheckInvoiceFormat
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ServiceInvoice::query();

        // Buscador por número de factora
        if ($request->has('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where('invoice_number', 'like', $searchTerm);
        }

        $this->sort(
            $query,
            $request,
            ['created_at', 'invoice_number', 'total']
        );

        return new ServiceInvoiceCollection($query->paginate(10));   
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ServiceInvoicesRequest $serviceInvoicesRequest)
    {
        if ($request->user()->role->name !== 'admin') {
            return response()->json(['message' => 'No tienes permiso de administrador'], 403);
        }
            
        $data = $serviceInvoicesRequest->validated();

        $serviceInvoice = ServiceInvoice::create($data);

        $formatCheck = $this->validateInvoiceFormat($serviceInvoice->invoice_number);

        if (!$formatCheck) {
            return response()->json(['message' => $formatCheck['message'], 403]);
        }

        return new ServiceInvoiceResource($serviceInvoice);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $serviceInvoice = ServiceInvoice::with('appointment')->find($id);
        if (!$serviceInvoice) {
            return response()->json(['message' => 'Factura de servicio no encontrada'], 403);
        }
        return new ServiceInvoiceResource($serviceInvoice);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id, ServiceInvoicesRequest $serviceInvoicesRequest)
    {
        if ($request->user()->role->name !== 'admin') {
            return response()->json(['message' => 'No tienes permiso de administrador'], 403);
        }

        $data = $serviceInvoicesRequest->validated();

        $serviceInvoice = ServiceInvoice::find($id);

        if (!$serviceInvoice) {
            return response()->json(['message' => 'Factura de servicio no encontrada']);
        }

        $serviceInvoice->update($data);

        return new ServiceInvoiceResource($serviceInvoice);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {
        if ($request->user()->role->name !== 'admin') {
            return response()->json(['message' => 'No tienes permiso de administrador'], 403);
        }

        $serviceInvoice = ServiceInvoice::find($id);

        if (!$serviceInvoice) {
            return response()->json(['message' => 'Factura de servicio no encontrada']);
        }

        $serviceInvoice->delete();

        return response()->json(['message' => 'Factura de servicio eliminada correctamente']);
    }

    public function sort($query, Request $request, array $allowedSorts, string $defaultSort = 'created_at', string $defaultOrder = 'desc'){
        $sortBy = $request->get('sort_by', $defaultSort);
        $sortOrder = $request->get('sort_order', $defaultOrder);

        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = $defaultSort;
        }
        return $query->orderBy($sortBy, $sortOrder);
    }

    public function validateInvoiceFormat(string $invoiceNumber){
        //Patron: SINV-XXXX
        $pattern = '/^SINV-\d{5}$/';

        if (!preg_match($pattern, $invoiceNumber)) {
            return [
                'valid' => false,
                'message' => 'El número de factura debe tener el formato SINV-XXXXX'
            ];
        }

        return [
            'valid' => true,
            'message' => ''
        ];
    }
}
