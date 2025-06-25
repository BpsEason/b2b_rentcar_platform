from pydantic import BaseModel
from typing import Optional

class TokenData(BaseModel):
    username: Optional[str] = None # 或 user_id
