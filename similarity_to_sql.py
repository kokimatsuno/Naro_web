import pandas as pd
import numpy as np
from sqlalchemy import create_engine
import pymysql

with open ("db_pass.txt", "r", encoding="utf-8") as f:
    dbpass = f.read()
    dbpass = dbpass.replace("\n", "")

engine = create_engine(f"mysql+pymysql://s19752km:{dbpass}@webdb.sfc.keio.ac.jp:3306/s19752km")


genre_list = [101, 102, 201, 202, 301, 302, 303, 304, 305, 306, 307, 401, 402, 403, 404, 9901, 9902, 9903, 9904, 9999, 9801]

for item in genre_list:
    print(f"start to_sql process in {item}")
    df = pd.read_csv(f"./calc_result/similarity_{item}.csv")
    df.to_sql(f"Naro_similarity_{item}", con=engine, if_exists='replace', index=False, method='multi')