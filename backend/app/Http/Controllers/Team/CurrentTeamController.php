<?php

namespace App\Http\Controllers\Team;

use Illuminate\Http\Request;
use Laravel\Jetstream\Http\Controllers\CurrentTeamController as JetstreamController;

class CurrentTeamController extends JetstreamController
{
    public function update(Request $request)
    {
        $response = parent::update($request);
        try {
            $route = app('router')->getRoutes()->match(app('request')->create(url()->previous()));
            $name = $route->getName();
            $params = $route->parameters();

            if (isset($params['team']) && $request->user()->currentTeam->id) {
                $params['team'] = $request->user()->currentTeam->id;
            }

            return redirect()->route($name, $params);
        } catch (\Throwable $th) {
            return redirect(config('fortify.home'));
        }
    }
}
