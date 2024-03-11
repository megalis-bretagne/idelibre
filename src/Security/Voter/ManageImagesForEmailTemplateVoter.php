<?php

namespace App\Security\Voter;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ManageImagesForEmailTemplateVoter
{
    public const LOCALHOST = "https://localhost";

    public function __construct(
        private readonly ParameterBagInterface $bag
    )
    {
    }

    protected function supports($attribute): bool
    {
        return $attribute == 'ALLOWED_ORIGINS' && ($attribute instanceof Request);
    }

    /**
     * @throws Exception
     */
    protected function voteOnAttribute($attribute , Request $request): Response
    {
        $origin = $request->server->get('HTTP_ORIGIN');

        if (!in_array($request ,$this->allowedOrigins()) ) {
            return new Response("Vous n'avez pas la permission d'accéder à cette ressource.", 403);
        }

        if ($origin && !in_array($origin, $this->allowedOrigins())){
            return new Response("Vous n'avez pas la permission d'accéder à cette ressource.", 403);
        }

        if ($request->isMethod("OPTIONS")) {
            return new Response("", 200, ["Access-Control-Allow-Methods" => "POST, OPTIONS"]);
        }
    }

    private function allowedOrigins()
    {
        return [self::LOCALHOST, $this->bag->get('base_url')];
    }

}
