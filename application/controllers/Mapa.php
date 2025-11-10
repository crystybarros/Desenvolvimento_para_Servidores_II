<?php
define('BASEPATH') OR exit('No direct script access allowed');

class Mapa extends CI_Controller {

    /*
    Validação dos tipos de retorno nas validações (Código de erro)
    1 - Operação realizada na banco de dados com sucesso(Inserção, Alteração, Consulta ou Exclusão)
    2 - Conteúdo passado nulo ou vazio
    3 - Conteúdo zerado
    4 - Conteúdo não inteiro
    5 - Conteúdo não é um texto
    6 - Data em formato inválido
    12 - Na atualização, pelo menos um atributo deve ser passado
    99 - Parâmetros passados do front não correspondem ao método
    */


    //Atributos privados da classe
    private $codigo;
    private $dataReserva;
    private $codigo_sala;
    private $codigo_horario;
    private $codigo_turma;
    private $codigo_professor;
    private $estatus;
    //Atributos para os mapeamentos
    private $dataInicio;
    private $dataFim;
    private $diaSemana;

    //Getters dos atributos
    public function getCodigo(){
        return $this->codigo;
    }
    public function getDataReserva(){
        return $this->dataReserva;
    }
    public function getCodigoSala(){
        return $this->codigo_sala;
    }
    public function getCodigoHorario(){
        return $this->codigo_horario;
    }
    public function getCodigoTurma(){
        return $this->codigo_turma;
    }
    public function getProfessor(){
        return $this->codigo_professor;
    }
    public function getEstatus(){
        return $this->estatus;
    }
    public function getDataInicio(){
        return $this->dataInicio;
    }
    public function getDataFim(){
        return $this->dataFim;
    }
    public function getDiaSemana(){
        return $this->diaSemana;
    }

    //Setters dos atributos
    public function setCodigo($codigoFront){
        $this->codigo = $codigoFront;
    }
    public function setDataReserva($dataReservaFront){
        $this->dataReserva = $dataReservaFront;
    }
    public function setCodigoSala($codigo_salaFront){
        $this->codigo_sala = $codigo_salaFront;
    }
    public function setCodigoHorario($codigo_horarioFront){
        $this->codigo_horario = $codigo_horarioFront;
    }
    public function setCodigoTurma($codigo_turmaFront){
        $this->codigo_turma = $codigo_turmaFront;
    }
    public function setCodigoProfessor($codigo_professorFront){
        $this->codigo_professor = $codigo_professorFront;
    }
    public function setEstatus($estatusFront){
        $this->estatus = $estatusFront;
    }
    public function setDataInicio($dataInicioFront){
        $this->dataInicio = $dataInicioFront;
    }
    public function setDataFim($dataFimFront){
        $this->dataFim = $dataFimFront;
    }

    public function setDiaSemana($diaSemanaFront){
        $this->diaSemana = $diaSemanaFront;
    }

    public function inserir() {
        //Atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "dataReserva" => '0',
                "codSala" => '0',
                "codHorario" => '0',    
                "codTurma" => '0',
                "codProfessor" => '0'
            ];
            
            if (verificarParam($resultado, $lista) != 1){
                //Validar vindos de forma correta do frontend(Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                //Validar campos quanto ao tipo de dado e tamanho (Helper)
                $retornoDataReserva = validarDados($resultado->dataReserva, 'date', true);
                $retornoCodSala = validarDados($resultado->codSala, 'int', true);
                $retornoCodHorario = validarDados($resultado->codHorario, 'int', true);
                $retornoCodTurma = validarDados($resultado->codTurma, 'int', true);
                $retornoCodProfessor = validarDados($resultado->codProfessor, 'int', true); 

                if($retornoDataReserva['codigoHelper'] != 0){
                    $erros[] = ['codigo' => $retornoDataReserva['codigoHelper'], 
                                'campo' => 'Data de Reserva',
                                'msg' => $retornoDataReserva['msg']];
                }

                if($retornoCodSala['codigoHelper'] != 0){
                    $erros[] = ['codigo' => $retornoCodSala['codigoHelper'], 
                                'campo' => 'Código da Sala',
                                'msg' => $retornoCodSala['msg']];
                }

                if($retornoCodHorario['codigoHelper'] != 0){
                    $erros[] = ['codigo' => $retornoCodHorario['codigoHelper'], 
                                'campo' => 'Código do Horário',
                                'msg' => $retornoCodHorario['msg']];
                }

                if($retornoCodTurma['codigoHelper'] != 0){
                    $erros[] = ['codigo' => $retornoCodTurma['codigoHelper'], 
                                'campo' => 'Código da Turma',
                                'msg' => $retornoCodTurma['msg']];
                }

                if($retornoCodProfessor['codigoHelper'] != 0){
                    $erros[] = ['codigo' => $retornoCodProfessor['codigoHelper'], 
                                'campo' => 'Código do Professor',
                                'msg' => $retornoCodProfessor['msg']];
                }

                //Se não encontrat erros
                if (empty($erros)){
                    $this->setDataReserva($resultado->dataReserva);
                    $this->setCodigoSala($resultado->codSala);
                    $this->setCodigoHorario($resultado->codHorario);
                    $this->setCodigoTurma($resultado->codTurma);
                    $this->setProfessor($resultado->codProfessor);
                    
                    $this->load->model('M_mapa');
                    $resBanco = $this->M_mapa->inserir(
                        $this ->getDataReserva(),
                        $this ->getCodigoSala(),   
                        $this ->getCodigoHorario(),
                        $this ->getCodigoTurma(),
                        $this ->getProfessor()
                    );

                    if ($resBanco['codigo'] == 1){
                        $sucesso = true;
                    } else {
                        //Captura do erro do Banco
                        $erros[] = [
                            'codigo' => $resBanco['codigo'], 
                            'msg' => $resBanco['msg']
                        ];
                    }

                }
            }
        } catch (Exception $e){
            $erros[] = ['codigo' => 98, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        //Monta retorno unico
        if ($sucesso == true) {
            $retorno = ['sucesso' => $suceso, 'codigo' => $resBanco['codigo'],
                        'msg' => $resBanco['msg']];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        //Transforma o array em JSON
        echo json_encode($retorno);
    }

    public function consultar() {
        //Atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "codigo" => '0',
                "dataReserva" => '0', 
                "codSala" => '0',
                "codHorario" => '0',    
                "codTurma" => '0',
                "codProfessor" => '0'
            ];
            
            if (verificarParam($resultado, $lista) != 1){
                //Validar vindos de forma correta do frontend(Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                //Validar campos quanto ao tipo de dado e tamanho (Helper)
                $retornoCodigo = validarDadosConsulta($resultado->codigo, 'int');
                $retornoDataReserva = validarDadosConsulta($resultado->dataReserva, 'date');
                $retornoCodSala = validarDadosConsulta($resultado->codSala, 'int');
                $retornoCodHorario = validarDadosConsulta($resultado->codHorario, 'int');
                $retornoCodTurma = validarDadosConsulta($resultado->codTurma, 'int');
                $retornoCodProfessor = validarDadosConsulta($resultado->codProfessor, 'int');


                if($retornoCodigo['codigoHelper'] != 0){
                    $erros[] = ['codigo' => $retornoCodigo['codigoHelper'], 
                                'campo' => 'Código',
                                'msg' => $retornoCodigo['msg']];
                }

                if($retornoDataReserva['codigoHelper'] != 0){
                    $erros[] = ['codigo' => $retornoDataReserva['codigoHelper'], 
                                'campo' => 'Data Início',
                                'msg' => $retornoDataReserva['msg']];
                }

                if($retornoCodSala ['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoCodSala ['codigoHelper']
                                'campo' => 'Codigo da Sala',
                                'msg' => $retornoCodSala['msg']];
                }

                if ($retornoCodHorario['codigoHelper'] !=0) {
                    $erros[] = ['codigo' => $retornoCodHorario['codigoHelper'],
                                'campo' => 'Codigo do Horário',
                                'msg' => $retornoCodHorario['msg']];
                }

                if($retornoCodTurma['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoCodProfessor['codigoHelper'],
                                'campo' => 'Codigo do Professor',
                                'msg' => $retornoCodProfessor['msg']];
                }

                //Se não encontrar erros
                if (empty($erros)) {
                    $this-> setCodigo($resultado->codigo);
                    $this->setDataReserva($resultado->dataReserva);
                    $this->setCodigoSala($resultado->codSala);
                    $this->setCodigoTurma($resultado->codTurma);
                    $this->setProfessor($resultado->codProfessor);

                    $this->load->model('M_mapa');
                    $resBanco = $this

                }
