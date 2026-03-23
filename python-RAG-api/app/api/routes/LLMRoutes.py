
from fastapi import APIRouter, Depends
from app.core.interfaces.ILLM import ILLM
from app.services.LLMImpl import LLMImpl

LLMrouter = APIRouter(prefix="/LLM", tags=["LLM"])

def get_llm() -> ILLM:
    return LLMImpl()

@LLMrouter.get("/")
def get_users():
    return {"users": ["Alice", "Bob"]}

@LLMrouter.get("/prompt")
async def input(prompt: str, llm: ILLM = Depends(get_llm)):

    respuesta = llm.send_prompt(f"{prompt}")
    return {"prompt": respuesta}

