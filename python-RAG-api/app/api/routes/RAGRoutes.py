
from fastapi import APIRouter, Depends
from app.core.interfaces.IRAGService import IRAGService
from app.services.ChromaImpl import ChromaImpl
from app.services.LLMImpl import LLMImpl
from app.services.RAGImpl import RAGImpl

RAGRouter = APIRouter(prefix="/RAG", tags=["RAG"])

def get_RAG() -> IRAGService:
    chroma = ChromaImpl()
    llm = LLMImpl()
    return RAGImpl(chroma=chroma, llm=llm)

@RAGRouter.get("/prompt")
def prompt(prompt: str,RagService: IRAGService = Depends(get_RAG)):
    return  RagService.rag_prompt(prompt=prompt)

