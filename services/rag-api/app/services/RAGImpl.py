from app.core.interfaces.IChroma import IChroma
from app.core.interfaces.ILLM import ILLM
from app.core.interfaces.IRAGService import IRAGService


class RAGImpl(IRAGService):
    def __init__(self, chroma: IChroma, llm: ILLM):
        self.chroma = chroma
        self.llm = llm

    def rag_prompt(self, prompt: str) -> str:
        result = self.chroma.queryCollection(prompt)

        context = "\n".join([r.document for r in result])
        prompt_completo = f"""Dado el siguiente contexto:
        {context}

        Responde la pregunta: {prompt}"""

        return self.llm.send_prompt(prompt_completo)
