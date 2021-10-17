# coding: utf-8

import pandas as pd
from sqlalchemy import create_engine
import pymysql
import datetime

print("start",datetime.datetime.now(), flush=True)

with open ("db_pass.txt", "r", encoding="utf-8") as f:
    dbpass = f.read()
    dbpass = dbpass.replace("\n", "")

engine = create_engine(f"mysql+pymysql://s19752km:{dbpass}@webdb.sfc.keio.ac.jp:3306/s19752km")

genre_list = [101, 102, 201, 202, 301, 302, 303, 304, 305, 306, 307, 401, 402, 403, 404, 9901, 9902, 9903, 9904, 9999, 9801]

for item in genre_list:
    print(item)
    sql = f"select ncode, title, keyword, story from Naro_All_info where genre = {item}"
    df = pd.read_sql(sql, con=engine)
    df.to_csv(f'./data/for_similarity_calc_{item}.csv', index=False)
    
    
print("end", datetime.datetime.now(), flush=True)