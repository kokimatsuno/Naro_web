# coding: utf-8

import MeCab
import numpy as np
import pandas as pd
from sklearn.metrics.pairwise import cosine_similarity
from gensim.models.doc2vec import Doc2Vec
import datetime

#形態素解析
def mecab_sep(text):
    m = MeCab.Tagger("-Ochasen")
    m.parse('')       #https://shogo82148.github.io/blog/2015/12/20/mecab-in-python3-final/   
    node = m.parseToNode(text)
    word_list = []
    while node:
        if node.feature.split(",")[0] == "名詞":
            word_list.append(node.surface)
        elif node.feature.split(",")[0] == "動詞":
            word_list.append(node.feature.split(",")[6])
        elif node.feature.split(",")[0] == "形容詞":
            word_list.append(node.feature.split(",")[6])
        elif node.feature.split(",")[0] == "形容動詞":
            word_list.append(node.feature.split(",")[6])     
        node = node.next
    return word_list


#Doc2Vec　文章のベクトル化
def calc_vecs_d2v(docs):
    vecs = []
    for d in docs:
        vecs.append(model.infer_vector(mecab_sep(d)))
    return vecs


###関数の実行###

if __name__ == "__main__":
    print("start",datetime.datetime.now(), flush=True)

    #モデルの読み込み
    #参照：https://yag-ays.github.io/project/pretrained_doc2vec_wikipedia/
    #gensim==3.8.1で動作
    model = Doc2Vec.load("./jawiki.doc2vec.dbow300d/jawiki.doc2vec.dbow300d.model")
    #処理が重い場合、プログラムが完了後に出力されることがある。flush=Trueにすると、即時printされる
    print("complete loading model", flush=True)

    genre_list = [101, 102, 201, 202, 301, 302, 303, 304, 305, 306, 307, 401, 402, 403, 404, 9901, 9902, 9903, 9904, 9999, 9801]

    for item in genre_list:
        print(f"start similarlity calculation in genre {item}", flush=True)
        df = pd.read_csv(f"./data/for_similarity_calc_{item}.csv")
        #対象文章・ncodeをリスト化する
        target_docs = df['story'].to_list()
        ncode_list = df['ncode'].to_list()
        
        #類似度の計算結果を入れる配列を定義
        similarity = []
        
        max_loop = (len(df) // 5000) + 1
        for i in range(max_loop):
            if i == max_loop-1:
                offset = 5000  * i
                docs_vecs = calc_vecs_d2v(target_docs[offset:])
            elif i == 0:
                offset = 5000 * i
                finish = 5000 * (i+1)
                docs_vecs = calc_vecs_d2v(target_docs[offset:finish])
                basic_vecs = [docs_vecs[0]]
            else:
                offset = 5000 * i
                finish = 5000 * (i+1)
                docs_vecs = calc_vecs_d2v(target_docs[offset:finish])
            
            similarity += cosine_similarity(basic_vecs, docs_vecs).tolist()[0]
        
        result_df = pd.DataFrame(list(zip(ncode_list, similarity)), columns= ['ncode', 'similarity'])
        result_df.to_csv(f'./calc_result/similarity_{item}.csv', index=False)

    print("end",datetime.datetime.now())