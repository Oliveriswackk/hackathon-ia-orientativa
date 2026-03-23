from fastapi import FastAPI
from app.api.routes.LLMRoutes import LLMrouter
from app.api.routes.RAGRoutes import RAGRouter
from app.api.routes.chromaRoutes import Chromarouter

app = FastAPI()

app.include_router(Chromarouter)
app.include_router(LLMrouter)
app.include_router(RAGRouter)
