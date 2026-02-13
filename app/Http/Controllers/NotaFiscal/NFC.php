<?php

namespace App\Http\Controllers\NotaFiscal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NotaFiscal;
use Symfony\Component\DomCrawler\Crawler;

class NotaFiscalController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validar se o HTML foi enviado
        $request->validate(['html' => 'required']);

        // 2. Salvar no banco
        $nota = NotaFiscal::create([
            'html_bruto' => $request->html,
            'status' => 'pendente'
        ]);

        // 3. Processar (Inicia o Parser)
        $resultado = $this->parser($nota);

        return response()->json([
            'message' => 'Nota importada e processada!',
            'itens_encontrados' => count($resultado),
            'dados' => $resultado
        ]);
    }

    private function parser(NotaFiscal $nota)
    {
        $crawler = new Crawler($nota->html_bruto);
        $produtos = [];

        // Seleciona os itens da lista baseado no HTML que você enviou
        $crawler->filter('#collapse1 .list-group-flush')->first()->filter('.list-group-item')->each(function (Crawler $node) use (&$produtos) {
            
            $nomeNode = $node->filter('p.h6');
            if ($nomeNode->count() > 0) {
                // Extrair Nome (pegando apenas o primeiro nó de texto antes do <small>)
                $nome = trim($nomeNode->getNode(0)->firstChild->textContent);
                
                // Extrair Código (dentro do <small>)
                $codigo = $nomeNode->filter('small')->count() > 0 
                    ? trim(str_replace(['(Cód:', ')'], '', $nomeNode->filter('small')->text())) 
                    : null;

                // Extrair Detalhes (Qtde, UN, Vl Unit) na segunda row
                $textoDetalhes = $node->filter('.row')->at(1)->text();
                
                preg_match('/Qtde\.:\s*([\d,.]+)/', $textoDetalhes, $qtd);
                preg_match('/Vl\. Unit\.:\s*([\d,.]+)/', $textoDetalhes, $vlUnit);

                $produtos[] = [
                    'nome' => $nome,
                    'codigo' => $codigo,
                    'quantidade' => isset($qtd[1]) ? (float)str_replace(',', '.', $qtd[1]) : 0,
                    'valor_unitario' => isset($vlUnit[1]) ? (float)str_replace(',', '.', $vlUnit[1]) : 0,
                ];
            }
        });

        // Atualiza a nota com os dados processados
        $nota->update([
            'dados_extraidos' => $produtos,
            'status' => 'processado'
        ]);

        return $produtos;
    }
}