<?php
defined('BASEPATH') or exit('No direct script access allowed');

//Incluir a classe que precisamos instanciar
include_once("M_sala.php");
include_once("M_horario.php");
include_once("M_turma.php");
include_once("M_professor.php");

class M_mapa extends CI_Model {
    /*
    Validação dos tipos de retornos nas validações (Código de erro)
    0 - Erro de exceção
    1 - Operação realizada no banco de dados com sucesso (iserção, alteração, consulta ou exclusão
    7 - Reserva desativada no sistema
    8 - A data está ocupada para esta sala
    9 - Houve algum problema de inserção, atualização, consulta ou exclusão
    10 - Reserva já cadastrada
    11 - Reserva não encontrada
    98 - Método auxiliar de consulta que não trouxe dados
    */

    public function inserir($dataReserva, $codSala, $codHorario, $codTurma, $codProfessor) {
        try {
            //verifica se a reserva já está cadastrada
            $retornoConsultaReservaTotal = $this->consultaReservaTotal($dataReserva, $codSala, $codHorario, $codTurma, $codProfessor);

            if ($retornoConsultaReservaTotal['codigo'] == 11 ||
                $retornoConsultaReservaTotal['codigo'] == 7) {
                //Chamo o objeto sala para validação
                $salaObj = new M_sala();

                //Chamar o método de verificação
                $retornoConsultaSala = $salaObj->consultaSala($codSala, '', '');

                if ($retornoConsultaSala['codigo'] == 10) {
                    //Chamo o objeto horario para validação 
                   $horarioObj = new M_horario();

                    //Chamar o método de verificação
                    $retornoConsultaHorario = $horarioObj->consultarHorario($codHorario, '', '');

                    if ($retornoConsultaHorario['codigo'] == 10) {
                        //Chamo o objeto turma para validação 
                        $turmaObj = new M_turma();

                        //Chamar o método de verificação
                        $retornoConsultaTurma = $turmaObj->consultaTurmaCod($codTurma);

                        if ($retornoConsultaTurma['codigo'] == 10) {
                            //Chamo o objeto professor para validação 
                            $professorObj = new M_professor();

                            //Chamar o método de verificação
                            $retornoConsultaProfessor = $professorObj->consultaProfessorCod($codProfessor, '', '', '');

                            if ($retornoConsultaProfessor['codigo'] == 1) {
                                //Query deinserção dos dados
                                $this->db->query("insert into tbl_mapa (datareserva, sala, codigo_horario, codigo_turma, codigo_professor)
                                    values ('" .$dataReserva . "', $codSala, $codHorario, $codTurma, $codProfessor)");

                                //verificar se a inserção ocorreu com sucesso
                                if ($this->db->affected_rows() > 0){
                                    $dados = array(
                                        'codigo' => 1,
                                        'msg' => 'Agendamento cadastrado corretamente'
                                    );
                                }else {
                                    $dados = array(
                                        'codigo' => 9,
                                        'msg' => 'Houve algum problema na inserção na tabela de agendamento.'
                                    );
                                }
                            } else {
                                $dados = array('codigo' => $retornoConsultaProfessor ['codigo'],
                                    'msg' => $retornoConsultaProfessor ['msg']);
                            }
                        } else {
                            $dados = array('codigo' => $retornoConsultaTurma ['codigo'],
                                'msg' => $retornoConsultaTurma ['msg']);
                        }
                    } else {
                        $dados = array('codigo' => $retornoConsultaHorario ['codigo'],
                            'msg' => $retornoConsultaHorario ['msg']);
                    }
            } else {
                $dados = array('codigo' => $retornoConsultaSala ['codigo'],
                    'msg' => $retornoConsultaSala ['msg']);
            }
            } else {
                $dados = array('codigo' => $retornoConsultaReservaTotal ['codigo'],
                    'msg' => $retornoConsultaReservaTotal ['msg']);
            }
        }catch (Exception $e) {
            $dados = array(
                'codigo' => 0,
                'msg' => 'ATENÇÃO: o seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }
        //Envia o array $dados com as informações tratadas
        //acima pela estrutura de decisão if    
        return $dados;
    }

    private function consultaReservaTotal($dataReserva, $codSala, $codHorario) {
        try {
            //Query para verificar a hora inicial e final daquele determinado horário
            $sql = "select * from tbl_horario
                    where codigo = $codHorario ";

            $retornoHorario = $this->db->query($sql);

            if ($retornoHorario->num_rows() > 0) {
                $linhaHr = $retornoHorario->row();
                $horaInicial = $linhaHr->hora_ini;
                $horaFinal = $linhaHr->hora_fim;

                //Query para consultar dados de acordo com parâmetro passados
                $sql = "select * from tbl_mapa m, tbl_horario h
                        where m.datareserva = '" . $dataReserva . "'
                            and m.sala = $codSala
                            and m.codigo_horario = h.codigo
                            and (h.hora_fim >= '" .$horaInicial . "'
                            and h.hora_ini <= '" . $horaFinal . "') ";

                $retornoMapa = $this->db->query($sql);

                //verifica se a consulta ocorreu com sucesso
                if ($retornoMapa->num_rows() > 0) {
                   $linha = $retornoMapa->row();

                   if (trim($linha->estatus) == "D") {

                        $dados = array(
                            'codigo'  => 7,
                            'msg' => 'Agendamento desativado do sistema.'
                        );

                    } else {
                        $dados = array(
                            'codigo' => 8,
                            'msg' => 'A data ' . $dataReserva . ' está ocupada para esta sala.'
                        );
                    }
                } else {
                    $dados = array(
                        'codigo' => 11,
                        'msg' => 'Reserva não encontrada.'
                    );
                }
            } else {
                $dados = array(
                    'codigo' => 11,
                    'msg' => 'Reserva não encontrada.'
                );
            }

        } catch (Exception $e) {
            $dados = array(
                'codigo' => 00,
                'msg' => 'ATENÇÃO: o seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }
        //Envia o array $dados com as informações tratadas
        //acima pela estrutura de decisão if    
        return $dados;
    }


    public function consultar($codigo, $dataReserva, $codSala, $codHorario, $codTurma, $codProfessor){
        try {
            //Query para consultar dados de acordo com parâmetro passados
            $sql = "select m.codigo, date_format(m.datareserva, '%d-%m-%Y') datareservabra, datareserva, 
                    m.sala, s.descricao descsala, m.codigo_horario,
                    h.descricao deshorario, m.codigo_turma, t.descricao descturma, m.codigo_professor, 
                    p.nome nome_professor
                    from tbl_mapa m, tbl_professor p, tbl_horario h, tbl_turma t, tbl_sala s
                    where m.estatus = '' 
                        and m.codigo_professor  = p.codigo
                        and m.codigo_horario    = h.codigo
                        and m.codigo_turma      = t.codigo
                        and m.sala              = s.codigo ";

            if (trim ($codigo) != '') {
                $sql = $sql . " and m.codigo = $codigo ";
            }

            if (trim ($dataReserva) != '') {
                $sql = $sql . " and m.datareserva = '" . $dataReserva . "' ";
            }

            if (trim ($codSala) != '') {
                $sql = $sql . " and m.sala = $codSala ";
            }

            if (trim ($codHorario) != '') {
                $sql = $sql . " and m.codigo_horario = $codHorario ";
            }

            if (trim ($codTurma) != '') {
                $sql = $sql . " and m.codigo_turma = $codTurma ";
            }

            if (trim ($codProfessor) != '') {
                $sql = $sql . " and m.codigo_professor = $codProfessor ";
            }

            $sql = $sql . " order by m.datareserva, h.hora_ini, m.codigo_horario, m.sala";

            $retorno = $this ->db->query($sql);

            //verifica se a consulta ocorreu com sucesso
            if ($retorno->num_rows() > 0) {
                $dados = array (
                    'codigo' => 1,
                    'msg' => 'Consulta efetuada com sucesso.',
                    'dados' => $retorno->result()
                );
            } else {
                $dados = array(
                    'codigo' => 11,
                    'msg' => 'Agendamento(s) não encontrado(s).'
                );
            }
        } catch (Exception $e) {
            $dados = array(
                'codigo' => 00,
                'msg' => 'ATENÇÃO: o seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }
        //Envia o array $dados com as informações tratadas
        //acima pela estrutura de decisão if
        return $dados;
    }

    public function alterar($codigo, $dataReserva, $codSala, $codHorario, $codTurma, $codProfessor){
        try {
            //verifico se a sala ja esta cadastrada
            $retornoConsulta = $this->consultar($codigo, "","","","","");

            if ($retornoConsulta['codigo'] == 1) {
                //inicio a query para atualizacao
                $query = "update tbl_mapa set ";

                //vamos comparar os itens
                if ($dataReserva !== '') {
                    $query .= "datareserva = '$dataReserva', ";
                }

                if ($codSala !== '') {
                    $salaObj = new M_sala();
                    $retornoConsultaSala = $salaObj->consultar($codSala,'', '', '');

                    if ($retornoConsultaSala['codigo'] == 1) {
                        $query .= "sala = $codSala, ";
                    }else {
                        $dados = array(
                            'codigo' => $retornoConsultaSala['codigo'],
                            'msg' => $retornoConsultaSala['msg']
                        );
                    }
                }

                if ($codHorario !== '') {
                    $horarioObj = new M_horario();
                    $retornoConsultaHorario = $horarioObj->consultarHorario($codHorario,'', '', '');

                    if ($retornoConsultaHorario['codigo'] == 1) {
                        $query .= "codigo_horario = $codHorario, ";
                    }else {
                        $dados = array(
                            'codigo' => $retornoConsultaHorario['codigo'],
                            'msg' => $retornoConsultaHorario['msg']
                        );
                    }
                }

                if ($codTurma !== '') {
                    $turmaObj = new M_turma();
                    $retornoConsultaTurma = $turmaObj->consultaTurmaCod($codTurma,'', '', '');

                    if ($retornoConsultaTurma['codigo'] == 1) {
                        $query .= "codigo_turma = $codTurma, ";
                    }else {
                        $dados = array(
                            'codigo' => $retornoConsultaTurma['codigo'],
                            'msg' => $retornoConsultaTurma['msg']
                        );
                    }
                }

                if ($codProfessor !== '') {
                    $professorObj = new M_professor();
                    $retornoConsultaProfessor = $professorObj->consultar($codProfessor,'', '', '');

                    if ($retornoConsultaProfessor['codigo'] == 1) {
                        $query .= "codigo_professor = $codProfessor, ";
                    }else {
                        $dados = array(
                            'codigo' => $retornoConsultaProfessor['codigo'],
                            'msg' => $retornoConsultaProfessor['msg']
                        );
                    }
                }

                //termino a concatenção da querry
                $queryFinal = rtrim($query, ", ") . " where codigo = $codigo";

                //Executo a query de atualizacao dos dados
                $this->db->query($queryFinal);

                //verificar se a atualizacao ocorreu com sucesso
                if ($this->db->affected_rows() > 0) {
                    $dados = array('codigo'=> 1, 'msg'=> 'Agendamento atualizado corretamente.');
                } else {
                    $dados = array('codigo' => 9, 'msg' => 'Houve algum problema na atualizacao na tabela de agendamento.');
                }
            }else {
                $dados = array('codigo' => $retornoConsulta['codigo'],
                                'msg' => $retornoConsulta['msg']);
            }
        } catch (Exception $e) {
            $dados = array('codigo'=> 00, 'msg'=> 'ATENÇÃO: O seguinte erro aconteceu -> '.$e->getMessage());
        }

        //envia o array $dados com as informações tratadas
        //acima pela estrutura de decisao if
        return $dados;
    }

    public function desativar($codigo) {
        try {
            //verifica se o agendamento já está cadastrado
            $retornoConsulta = $this->consultar(
                $codigo,
                "",
                "",     
                "",
                "",
                ""
            );

            if ($retornoConsulta ['codigo'] == 1) {
                //query de atualização dos dados
                $this->db->query("update tbl_mapa set estatus = 'D' where codigo = $codigo");
                
                //verifica se a atualização ocorreu com sucesso
                if ($this->db->affected_rows() > 0) {
                    $dados = array(
                        'codigo' => 1,
                        'msg' => 'Agendamento DESATIVADO corretamente.'
                    );
                } else {
                    $dados = array(
                        'codigo' => 9,
                        'msg' => 'Houve algum problema na DESATIVAÇÃO do agendamento.'
                    );
                }
            } else {
                $dados = array ('codigo' => $retornoConsulta ['codigo'],
                        'msg' => $retornoConsulta ['msg']);
            }

        } catch (Exception $e) {
            $dados = array(
                'codigo' => 00,
                'msg' => 'ATENÇÃO: o seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }
        //envia o array $dados com as informações tratadas
        //acima pela estrutura de decisão if
        return $dados;
    }
}
?>

               