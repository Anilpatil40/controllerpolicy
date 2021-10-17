<?php

namespace App\Http\Middleware;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Controller;
use App\Policies\HomeControllerPolicy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request as FacadesRequest;
use ReflectionMethod;

class ControllerPolicy
{
    private static $isFromBlade = false;

    protected $policies = [
         HomeController::class => HomeControllerPolicy::class,
    ];
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $this->regesterPolicies();

        return $next($request);
    }

    public function regesterPolicies(){

        foreach($this->policies as $controller => $policy){
            $policyClass = new $policy();
            foreach(get_class_methods($controller) as $method){
                if (!in_array($method, get_class_methods(Controller::class)) && $method != '__construct'){
                    Gate::define(class_basename($controller).'.'.$method, function($user, ...$params) use($policyClass, $method) {
                        if(!in_array($method, get_class_methods($policyClass::class))){
                            return false;
                        }

                        $args = ControllerPolicy::$isFromBlade ? $params : FacadesRequest::route()->parameters();
                        $args['user'] = $user;
                        

                        foreach((new ReflectionMethod($policyClass, $method))->getParameters() as $param){
                            if(!isset($args[$param->getName()])){
                                return false;
                            }
                        }

                        return call_user_func_array(array($policyClass, $method), $args);
                    });
                }
            }
        }

        $action = explode('@', FacadesRequest::route()->getAction()['controller']);

        if(!Gate::allows(class_basename($action[0]).'.'.$action[1])){
            abort(403);
        }

        ControllerPolicy::$isFromBlade = true;
    }
}
