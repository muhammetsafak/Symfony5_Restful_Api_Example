<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ResponseService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthController extends AbstractController
{
    protected $responseService;
    protected $userRepository;

    public function __construct(ResponseService $responseService, UserRepository $userRepository)
    {
        $this->responseService = $responseService;
        $this->userRepository = $userRepository;
    }

    public function register(Request $request)
    {
        $req = $this->responseService->transformJsonBody($request);
        $name = $req->get('name');
        $mail = $req->get('mail');
        $pass = $req->get('password');
        if(empty($name) || empty($mail) || empty($pass)){
            return $this->responseService->respondValidationError('İsim, Mail ve Şifre belirtilmelidir');
        }
        if(filter_var($mail, FILTER_VALIDATE_EMAIL) === FALSE){
            return $this->responseService->respondValidationError('Mail, geçerli değildir');
        }

        $check = $this->userRepository->findByMail($mail);
        if(!empty($check)){
            return $this->responseService->respondValidationError('Bu mail adresi zaten kullanılıyor.');
        }

        $this->userRepository->add($name, $mail, $pass);
        return $this->responseService->respondWithSuccess('Kullanıcı başarılı bir şekilde oluşturuldu.');
    }

    public function getToken(JWTTokenManagerInterface $JWTTokenManager): JsonResponse
    {
        $user = new User();
        return new JsonResponse([
            'token'     => $JWTTokenManager->create($user),
        ]);
    }
}
