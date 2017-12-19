<?php

namespace Jsdecena\LPM\Middleware;

//Modified my Suraj-PC on 19-Dec-2017

use App\ArrangmentUser;
use App\User;

use Illuminate\Http\Request;

class ProviderDetectorMiddleware
{
    /**
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $dmt = User::where('email',$request->username)->first();
        if($dmt){
            $request['provider'] = 'users';
            $request['verified'] = $dmt->email_verified;
            if($dmt->user_type == 1){
                $request['url'] = 'api/matrimony/';
            }else{
                $request['url'] = 'api/dating/';
            }
        }else{
            $ar = ArrangmentUser::where('email',$request->username)->first();
            if($ar){
                $request['provider'] = 'ar';
                $request['verified'] = $ar->verified;
                $request['url'] = 'api/arrangement/';
            }else{
                $request['provider'] = 'invalid';
            }
        }

        $validator = validator()->make($request->all(), [
            'username' => 'required',
            'provider' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->getMessageBag(),
                'status_code' => 422
            ], 422);
        }

        config(['auth.guards.api.provider' => $request->input('provider')]);

        return $next($request);
    }
}
