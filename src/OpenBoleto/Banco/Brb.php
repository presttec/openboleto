<?php

/*
 * OpenBoleto - Geração de boletos bancários em PHP
 *
 * LICENSE: The MIT License (MIT)
 *
 * Copyright (C) 2013 Estrada Virtual
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this
 * software and associated documentation files (the "Software"), to deal in the Software
 * without restriction, including without limitation the rights to use, copy, modify,
 * merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies
 * or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace OpenBoleto\Banco;

use OpenBoleto\BoletoAbstract;

/**
 * Classe boleto BRB - Banco de Brasília.
 *
 * @package    OpenBoleto
 * @author     Daniel Garajau <http://github.com/kriansa>
 * @copyright  Copyright (c) 2013 Estrada Virtual (http://www.estradavirtual.com.br)
 * @license    MIT License
 * @version    1.0
 */
class Brb extends BoletoAbstract
{
    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = '070';

    /**
     * Localização do logotipo do banco, referente ao diretório de imagens
     * @var string
     */
    protected $logoBanco = 'brb.png';

    /**
     * Linha de local de pagamento
     * @var string
     */
    protected $localPagamento = 'Pagar preferencialmente em conveniencia BRB';

    /**
     * Define as carteiras disponíveis para este banco
     * @var array
     */
    protected $carteiras = array('1', '2');

    /**
     * Define os nomes das carteiras para exibição no boleto
     * @var array
     */
    protected $carteirasNomes = array('1' => 'COB', '2' => 'COB');

    /**
     * Nome do arquivo de template a ser usado
     *
     * A Caixa obriga-nos a usar campos não presentes no projeto original, além de alterar cedente
     * para beneficiário e sacado para pagador. Segundo o banco, estas regras muitas vezes não são
     * observadas na homologação, mas, considerando o caráter subjetivo de quem vai analisar na Caixa,
     * preferi incluir todos de acordo com o manual. Para conhecimento, foi copiado o modelo 3.5.1 adaptado
     * Também removi os campos Espécie, REAL, Quantidade e Valor por considerar desnecessários e não obrigatórios
     *
     * @var string
     */
    protected $layout = 'brb.phtml';


    /**
     * Gera o Nosso Número.
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        return substr($this->getCampoLivre(), 13);
    }

    /**
     * Método para gerar o código da posição de 20 a 44
     *
     * @return string
     */
    public function getCampoLivre()
    {
        $chave = '000' . static::zeroFill($this->getAgencia(), 3) .
            static::zeroFill($this->getConta() . $this->getContaDv(), 7) .
            $this->getCarteira() .
            static::zeroFill($this->getSequencial(), 6) .
            $this->getCodigoBanco();
        $d1 = static::modulo10($chave);

        CalculaD2:
        $modulo = static::modulo11($chave . $d1, 7);

        if ($modulo['resto'] == 0) {
            $d2 = 0;
        } else if ($modulo['resto'] > 1) {
            $d2 = 11 - $modulo['resto'];
        } else if ($modulo['resto'] == 1) {
            $d1 = $d1 + 1;
            if ($d1 == 10) {
                $d1 = 0;
            }
            goto CalculaD2;
        }

        return $chave . $d1 . $d2;
    }

    /**
     * Retorna o campo Agência/Cedente do boleto
     *
     * @return string
     */
    public function getAgenciaCodigoCedente()
    {
        return $this->getAgencia() . ' - ' . $this->getConta() . ' - ' . $this->getContaDv();
    }
    
    /**
     * Define o número da conta
     *
     * @param int $conta
     * @return BoletoAbstract
     */
    public function setConta($conta)
    {
        $this->conta = substr($conta, -6);
        return $this;
    }
}
