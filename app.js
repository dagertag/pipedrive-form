document.getElementById("open-modal").addEventListener("click", function () {
    if (!window.Pipedrive) {
        console.error("Pipedrive SDK не загружен");
        return;
    }

    const client = new Pipedrive.SDK();

    client.init().then(() => {
        client.modal.open({
            url: "https://pipedrive-form.onrender.com/", // ссылка на твою форму
            width: "800px",
            height: "600px",
            title: "Моя форма"
        });
    }).catch((error) => {
        console.error("Ошибка инициализации Pipedrive SDK", error);
    });
});