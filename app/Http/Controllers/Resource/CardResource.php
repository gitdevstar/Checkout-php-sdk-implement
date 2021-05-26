<?php

namespace App\Http\Controllers\Resource;

use Illuminate\Http\Request;

use Exception;
// use Auth;

use App\Models\Card;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CheckoutApiController;

class CardResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try{

            $cards = Card::all();
			// $cards = Card::all();
            return response()->json($cards);

        } catch(Exception $e){
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
                'number' => 'required',
                'month' => 'required',
                'year' => 'required',
            ]);

        try{

			$card = (new CheckoutApiController)->addCard($request);

            Card::create([
                'card_id' => $card["token"],
                'last_four' => $card["last4"],
                'holder_name' => $card["name"],
                'brand' => $card["scheme"],
            ]);

            return response()->json(['message' => 'Card Added Successfully', 'success' => true]);

        } catch(Exception $e){
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {

        $this->validate($request,[
            'card_id' => 'required|exists:cards,card_id',
        ]);

        try{

			$result = (new CheckoutApiController)->deleteCard($id);

			if($result['success']){
				Card::where('card_id',$id)->delete();
					return response()->json(['message' => 'Card Deleted']);
			}

        } catch(Exception $e){
            return response()->json($e->getMessage(), 500);
        }
    }



}
